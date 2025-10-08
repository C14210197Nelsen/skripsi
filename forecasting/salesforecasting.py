## **FASE PERSIAPAN SISTEM DAN DATA**

import sys, math, warnings, pandas as pd, mysql.connector, json
import numpy as np
from datetime import date
from statsmodels.tsa.arima.model import ARIMA
from sklearn.metrics import mean_absolute_error
from dateutil.relativedelta import relativedelta
import matplotlib.pyplot as plt

# Aktifkan fitur warnings agar peringatan dari ARIMA dan pandas diabaikan
warnings.filterwarnings("ignore")

# Data database
DB_CONFIG = dict(
    host="localhost", 
    user="root", 
    password="", 
    database="gwanglobaldigital"
    )

# Function untuk cek apakah nilai merupakan angka bulat
def is_finite_number(x): 
    try: return math.isfinite(float(x))
    except: return False

def mean_absolute_percentage_error(y_true, y_pred):
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    eps = 1e-6
    mask = y_true > eps
    if mask.sum() == 0:
        return np.nan
    return np.nanmean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100



def print_summary(label, result):
    print(f"\n=== Model Terbaik Berdasarkan {label} ===")
    if result.get('order') is None:
        print("Tidak ada model valid.")
        return
    print(f"Order: {result['order']}")
    print(f"MAE: {result.get('mae', float('nan')):.4f}")
    print(f"MAPE: {result.get('mape', float('nan')):.4f}%")


def main():
    # Default Output = Error
    output = {"status":"error","message":""}

    try:
        # Mengambil kode produk dari command-line (Co : python train_model.py 92)
        if len(sys.argv)<=1: 
            raise Exception("Product ID diperlukan")
        product_id = int(sys.argv[1])

        # Koneksi ke database
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor(dictionary=True)
        
        # Query SQL ambil data semua sales product tertentu
        cursor.execute("""
            SELECT DATE_FORMAT(s.salesDate,'%Y-%m') AS bulan,
                    SUM(d.quantity) AS jumlah
            FROM salesorder s
            JOIN salesdetail d ON s.salesID=d.SalesOrder_salesID
            WHERE d.Product_productID=%s
            GROUP BY bulan ORDER BY bulan
        """, (product_id,))
        df = pd.DataFrame(cursor.fetchall())

        # Validasi jika data tidak ada atau kurang
        if df.empty or len(df)<18: 
            raise Exception("Data tidak cukup untuk forecasting")
        
        # Cek gap dari bulan terakhir transaksi
        df["bulan"] = pd.to_datetime(df["bulan"], errors="coerce")
        last_txn_month = df["bulan"].max()
        now_month = pd.Timestamp.today().to_period("M").to_timestamp()

        gap = (now_month.year - last_txn_month.year) * 12 + (now_month.month - last_txn_month.month)
        if gap >= 7:
            raise Exception("Produk tidak terjual selama 7 bulan terakhir, forecasting tidak dapat dilakukan.")

        # Preprocessing data
        df["jumlah"] = pd.to_numeric(df["jumlah"],errors="coerce").fillna(0)
        df["bulan"] = pd.to_datetime(df["bulan"],errors="coerce")
        df = df.dropna(subset=["bulan"]).sort_values("bulan")
        y = df.set_index("bulan")["jumlah"].astype(float)

        # Split train-test
        split_idx = int(len(y)*0.8)
        train, test = y.iloc[:split_idx], y.iloc[split_idx:]
        # pastikan test minimal 3
        if len(test) < 3:
            if len(y) >= 6:
                train, test = y.iloc[:-3], y.iloc[-3:]
            else:
                raise Exception("Data histori terlalu pendek (butuh minimal 6 bulan untuk split yang stabil).")



        # Grid search ARIMA
        mean_actual = np.mean(test)
        best_mae_model = {'order': None, 'model': None, 'forecast': None, 'mae': np.inf, 'mape': np.nan}
        best_mape_model = {'order': None, 'model': None, 'forecast': None, 'mae': np.inf, 'mape': np.inf}


        
        for p in range(16):
            for d in range(4):
                for q in range(16):
                    try:
                        print(f"Evaluating order: ({p},{d},{q})")

                        m=ARIMA(train,order=(p,d,q)).fit()

                        fcst=m.forecast(steps=len(test))
                        
                        mae = mean_absolute_error(test, fcst)
                        mape = mean_absolute_percentage_error(test, fcst)

                        # Update Best MAE model
                        if mae < best_mae_model['mae']:
                            best_mae_model = {
                                'order': (p, d, q),
                                'model': m,
                                'forecast': fcst,
                                'mae': mae,
                                'mape': mape,
                                }

                        # Update Best MAPE model
                        if not np.isnan(mape) and mape < best_mape_model['mape']:
                            best_mape_model = {
                                'order': (p, d, q),
                                'model': m,
                                'forecast': fcst,
                                'mae': mae,
                                'mape': mape,
                                }


                    except Exception as e: print(f"Error with order ({p}, {d}, {q}): {e}")
        
        if best_mae_model['order'] is None:
            best_mae_model = {'order': (1,1,0), 'model': None, 'forecast': None, 'mae': np.inf, 'mape': np.nan}
        if best_mape_model['order'] is None:
            best_mape_model = best_mae_model  # fallback

        # ambil nama produk
        cursor.execute("SELECT productName FROM product WHERE productID = %s", (product_id,))
        result = cursor.fetchone()
        product_name = result["productName"] if result else f"Produk {product_id}"

        # ambil nama produk
        cursor.execute("SELECT productName FROM product WHERE productID = %s", (product_id,))
        result = cursor.fetchone()
        product_name = result["productName"] if result else f"Produk {product_id}"

        # tampilkan nama produk di terminal
        print(f"\n=== {product_name} ===")


        print_summary("MAE", best_mae_model)
        print_summary("MAPE", best_mape_model)
        print(f"\nMean nilai aktual (testing): {mean_actual:.2f}")

        # =============================
        # Plot Perbandingan Prediksi
        # =============================
        plt.figure(figsize=(12, 7))
        plt.plot(test.index, test, label='Data Aktual', color='black')

        plt.plot(test.index, best_mae_model['forecast'], label='Prediksi MAE Terbaik', linestyle='--', color='red')
        plt.plot(test.index, best_mape_model['forecast'], label='Prediksi MAPE Terbaik', linestyle='--', color='blue')

        # plt.plot(test.index, forecast, label='Prediksi Terbaik', linestyle='--', color='purple')

        plt.title('Perbandingan Model ARIMA Berdasarkan MAE, MAPE, dan Kombinasi')
        plt.xlabel(product_name)
        plt.ylabel('Jumlah Penjualan')
        plt.legend()
        plt.grid(True)
        plt.tight_layout()
        plt.show()
    except Exception as e: print(f"{e}")

    finally:
        try:
            cursor.close()
            db.close()
        except:
            pass



if __name__ == "__main__":
    main()

