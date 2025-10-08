# Import Library
# sys = command-line
# math = fungsi matematika
# warnings = ignore ARIMA & pandas warnings
# mysql.connector = Konek ke database
# json = output json
# statsmodels = ARIMA
# sklearn.metrics = MAE
# dateutil = Bulan

import sys, math, warnings, pandas as pd, mysql.connector, json
import numpy as np
from datetime import date
from statsmodels.tsa.arima.model import ARIMA
from sklearn.metrics import mean_absolute_error
from dateutil.relativedelta import relativedelta

# Aktifkan fitur warnings agar peringatan dari ARIMA dan pandas diabaikan
warnings.filterwarnings("ignore")

# Data database
DB_CONFIG = dict(
    host="localhost", 
    user="root", 
    password="", 
    database="gwanglobaldigital"
    )


def mean_absolute_percentage_error(y_true, y_pred):
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    nonzero_mask = y_true > 1e-6  # abaikan data aktual yang terlalu kecil
    if np.sum(nonzero_mask) == 0:
        return 0.0
    return np.mean(np.abs((y_true[nonzero_mask] - y_pred[nonzero_mask]) / y_true[nonzero_mask])) * 100



# Function untuk cek apakah nilai merupakan angka bulat
def is_finite_number(x): 
    try: return math.isfinite(float(x))
    except: return False

# Function main
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
        if gap >= 5:
            raise Exception("Produk tidak terjual selama 3 bulan terakhir, forecasting tidak dapat dilakukan.")

        # Preprocessing data
        df["jumlah"] = pd.to_numeric(df["jumlah"],errors="coerce").fillna(0)
        df["bulan"] = pd.to_datetime(df["bulan"],errors="coerce")
        df = df.dropna(subset=["bulan"]).sort_values("bulan")
        y = df.set_index("bulan")["jumlah"].astype(float)


        # Split train-test
        split_idx=int(len(y)*0.9)
        train,test=y.iloc[:split_idx],y.iloc[split_idx:]
        if len(test)==0: train,test=y.iloc[:-1],y.iloc[-1:]
        avg_sales = float(test.mean())

        print("Rata-rata data uji:", float(test.mean()))
        print("Nilai aktual terkecil:", float(test.min()))
        print("Nilai aktual terbesar:", float(test.max()))
        print("Jumlah data aktual <= 1:", (test <= 1).sum())


        # Grid search ARIMA
        best_mae=float("inf")
        best_mape=float("inf")
        best_order=None
        for p in range(16):
            for d in range(4):
                for q in range(16):
                    try:
                        print(f"Evaluating order: ({p},{d},{q})")
                        m=ARIMA(train,order=(p,d,q)).fit()
                        fcst=m.forecast(steps=len(test))
                        mae = mean_absolute_error(test, fcst)
                        mape = mean_absolute_percentage_error(test, fcst)
                        if is_finite_number(mae) and mae < best_mae:
                            best_mae = float(mae)
                            best_mape = float(mape)
                            best_order = (p,d,q)
                    except: continue
        if best_order is None: 
            best_order=(1,1,0); best_mae=0.0
            print(f"[WARNING] Grid search ARIMA gagal, menggunakan default model {best_order} dengan MAE={best_mae}")


        today = date.today()  # menghasilkan objek date, misal 2025-08-24
        today_str = today.strftime("%Y-%m-%d")  # ubah ke string "YYYY-MM-DD"

        # Simpan best model metadata ke DB
        cursor.execute("""
            INSERT INTO sales_forecast (productID, forecast_month, forecast_quantity, p, d, q, MAE, MAPE, avg_sales)
            VALUES (%s, %s, NULL, %s, %s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE
                forecast_quantity=NULL,
                p=VALUES(p), d=VALUES(d), q=VALUES(q),
                MAE=VALUES(MAE), MAPE=VALUES(MAPE), avg_sales=VALUES(avg_sales),
                updated_at=NOW()
        """,(product_id, today_str, best_order[0], best_order[1], best_order[2], best_mae, best_mape, avg_sales))

        db.commit()

        # Forecast 2 bulan ke depan
        model_forecast = ARIMA(y, order=best_order).fit()  # fit model penuh
        forecast_values = model_forecast.forecast(steps=2)  # hasil array [bulan1, bulan2]

        forecasts = []
        last_date = y.index.max()  # tanggal terakhir data historis
        for i, fcst_value in enumerate(forecast_values, start=1):
            forecast_qty = max(0, round(float(fcst_value)))  # validasi tidak negatif
            forecast_month = (last_date + relativedelta(months=i)).strftime("%Y-%m-01")
            
            forecasts.append({"month": forecast_month, "qty": forecast_qty})
            
            # Simpan forecast ke DB
            cursor.execute("""
                INSERT INTO sales_forecast (productID, forecast_month, forecast_quantity, p, d, q, MAE, MAPE, avg_sales)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                    forecast_quantity=VALUES(forecast_quantity),
                    p=VALUES(p), d=VALUES(d), q=VALUES(q),
                    MAE=VALUES(MAE), MAPE=VALUES(MAPE), avg_sales=VALUES(avg_sales),
                    updated_at=NOW()
            """, (product_id, forecast_month, forecast_qty, best_order[0], best_order[1], best_order[2], best_mae, best_mape, avg_sales))

        db.commit()


        output={
            "status":"success",
            "productID":product_id,
            "model":{
                "p":best_order[0],
                "d":best_order[1],
                "q":best_order[2],
                "mae":best_mae,
                "mape": best_mape,
                "avg_sales": avg_sales
                },
            "forecast_next_2_months":forecasts}

    except Exception as e:
        output["message"]=str(e)
    finally:
        try: cursor.close(); db.close()
        except: pass
    print(json.dumps(output))

if __name__=="__main__":
    main()
