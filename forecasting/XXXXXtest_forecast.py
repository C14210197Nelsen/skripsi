# test_forecast_db.py
import sys, warnings, pandas as pd, mysql.connector, numpy as np
from statsmodels.tsa.arima.model import ARIMA
from sklearn.metrics import mean_absolute_error, mean_squared_error
import matplotlib.pyplot as plt
from dateutil.relativedelta import relativedelta
from statsmodels.tsa.stattools import adfuller

warnings.filterwarnings("ignore")

DB_CONFIG = dict(
    host="localhost",
    user="root",
    password="",
    database="gwanglobaldigital"
)

def main():
    try:
        if len(sys.argv) <= 1:
            raise Exception("Product ID diperlukan")
        product_id = int(sys.argv[1])
        namaproduk = sys.argv[2] if len(sys.argv) > 2 else f"Produk {product_id}"

        # Koneksi DB
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor(dictionary=True)

        # Ambil data penjualan bulanan dari DB
        cursor.execute("""
            SELECT DATE_FORMAT(s.salesDate, '%Y-%m') AS bulan,
                   SUM(d.quantity) AS jumlah
            FROM salesorder s
            JOIN salesdetail d ON s.salesID=d.SalesOrder_salesID
            WHERE d.Product_productID=%s
            GROUP BY bulan
            ORDER BY bulan
        """, (product_id,))
        df = pd.DataFrame(cursor.fetchall())
        if df.empty:
            raise Exception("Tidak ada data penjualan untuk produk ini.")

        # Preprocessing
        df["jumlah"] = pd.to_numeric(df["jumlah"], errors="coerce").fillna(0)
        df["bulan"] = pd.to_datetime(df["bulan"] + "-01")
        df = df.set_index("bulan").sort_index()

        # Split train-test
        split_idx = int(len(df) * 0.8)
        train, test = df.iloc[:split_idx], df.iloc[split_idx:]
        if len(test) == 0:
            train, test = df.iloc[:-1], df.iloc[-1:]

        # Grid search ARIMA
        p_values = range(0, 5)
        d_values = range(0, 3)
        q_values = range(0, 5)

        best_mae_model = {'mae': np.inf}
        best_rmse_model = {'rmse': np.inf}
        best_both_model = {'mae': np.inf, 'rmse': np.inf}

        for p in p_values:
            for d in d_values:
                for q in q_values:
                    try:
                        model = ARIMA(train['jumlah'], order=(p,d,q))
                        model_fit = model.fit()
                        forecast = model_fit.forecast(steps=len(test))
                        mae = mean_absolute_error(test['jumlah'], forecast)
                        mse = mean_squared_error(test['jumlah'], forecast)
                        rmse = np.sqrt(mse)
                        aic = model_fit.aic

                        if mae < best_mae_model['mae']:
                            best_mae_model = {'order': (p,d,q), 'model': model_fit, 'forecast': forecast, 'mae': mae, 'mse': mse, 'rmse': rmse, 'aic': aic}
                        if rmse < best_rmse_model['rmse']:
                            best_rmse_model = {'order': (p,d,q), 'model': model_fit, 'forecast': forecast, 'mae': mae, 'mse': mse, 'rmse': rmse, 'aic': aic}
                        if mae < best_both_model['mae'] and rmse < best_both_model['rmse']:
                            best_both_model = {'order': (p,d,q), 'model': model_fit, 'forecast': forecast, 'mae': mae, 'mse': mse, 'rmse': rmse, 'aic': aic}

                    except Exception as e:
                        print(f"Error ARIMA({p},{d},{q}): {e}")

        # Tampilkan hasil
        def print_summary(label, result):
            print(f"\n=== Model Terbaik Berdasarkan {label} ===")
            print(f"Order: {result['order']}")
            print(f"AIC: {result['aic']:.2f}")
            print(f"MAE: {result['mae']:.2f}")
            print(f"MSE: {result['mse']:.2f}")
            print(f"RMSE: {result['rmse']:.2f}")

        print_summary("MAE", best_mae_model)
        print_summary("RMSE", best_rmse_model)
        print_summary("MAE && RMSE", best_both_model)

        # Plot perbandingan
        plt.figure(figsize=(12,7))
        plt.plot(test.index, test['jumlah'], label='Data Aktual', color='black')
        plt.plot(test.index, best_mae_model['forecast'], label='Prediksi MAE Terbaik', linestyle='--', color='red')
        plt.plot(test.index, best_rmse_model['forecast'], label='Prediksi RMSE Terbaik', linestyle='--', color='blue')
        plt.plot(test.index, best_both_model['forecast'], label='Prediksi MAE & RMSE Terbaik', linestyle='--', color='purple')

        plt.title(f'Perbandingan Model ARIMA - {namaproduk}')
        plt.xlabel('Bulan')
        plt.ylabel('Jumlah Penjualan')
        plt.legend()
        plt.grid(True)
        plt.tight_layout()
        plt.show()

    except Exception as e:
        print(f"Error: {e}")
    finally:
        try: cursor.close(); db.close()
        except: pass

if __name__ == "__main__":
    main()
