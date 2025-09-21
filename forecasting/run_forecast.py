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
from statsmodels.tsa.arima.model import ARIMA
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

        # Query SQL Ambil model terakhir ARIMA
        cursor.execute("""
        SELECT p,d,q,MAE FROM sales_forecast
        WHERE productID=%s AND forecast_quantity IS NULL
        ORDER BY updated_at DESC LIMIT 1
        """,(product_id,))
        model_row=cursor.fetchone()

        # if not model_row: 
        #     try:
        #         import subprocess, os
        #         TRAIN_MODEL_PATH = os.path.join(os.path.dirname(__file__), "train_model.py")

        #         result = subprocess.run(
        #             ["python", TRAIN_MODEL_PATH, str(product_id)],
        #             capture_output=True, text=True
        #         )

        #         try:
        #             train_output = json.loads(result.stdout.strip())
        #         except:
        #             train_output = {"status": "error", "message": result.stderr.strip() or "Unknown error"}

        #         if train_output.get("status") != "success":
        #             raise Exception(f"Training otomatis gagal: {train_output.get('message', 'Tidak diketahui')}")

        #         cursor.close()
        #         db.close()
        #         db = mysql.connector.connect(**DB_CONFIG)
        #         cursor = db.cursor(dictionary=True)
                
        #         # Setelah training, ambil ulang model
        #         cursor.execute("""
        #             SELECT p,d,q,MAE FROM sales_forecast
        #             WHERE productID=%s AND forecast_quantity IS NULL
        #             ORDER BY updated_at DESC LIMIT 1
        #         """, (product_id,))
        #         model_row = cursor.fetchone()

        #         if not model_row:
        #             raise Exception("Training gagal. Model tidak tersedia.")

        #     except Exception as e:
        #         raise Exception(str(e))

        p,d,q,mae=model_row["p"],model_row["d"],model_row["q"],model_row.get("MAE",0)

        # Query SQL Ambil data sales produk tertentu
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
        if gap >= 3:
            raise Exception("Produk tidak terjual selama 3 bulan terakhir, forecasting tidak dapat dilakukan.")
    
        # Preprocessing data
        df["jumlah"] = pd.to_numeric(df["jumlah"],errors="coerce").fillna(0)
        df["bulan"] = pd.to_datetime(df["bulan"],errors="coerce")
        df = df.dropna(subset=["bulan"]).sort_values("bulan")
        y = df.set_index("bulan")["jumlah"].astype(float)

        # Forecast 2 bulan ke belakang
        steps_back = 5
        if len(y) <= steps_back:
            raise Exception("Data tidak cukup untuk backtesting")
        y_train_back = y.iloc[:-steps_back]
        y_test_back = y.iloc[-steps_back:]

        model_back = ARIMA(y_train_back, order=(p,d,q)).fit()
        forecast_back = model_back.forecast(steps=steps_back)
        forecast_back = [max(0, round(float(f))) for f in forecast_back]

        # Forecast 2 bulan ke depan (mirip train_model)
        model_forward  = ARIMA(y, order=(p,d,q)).fit()  # fit model penuh
        forecast_forward_values = model_forward.forecast(steps=2)

        last_date = y.index.max()
        forecasts_forward = []
        for i, fcst in enumerate(forecast_forward_values, start=1):
            forecast_qty = max(0, round(float(fcst)))
            forecast_month = (last_date + relativedelta(months=i)).strftime("%Y-%m-01")
            forecasts_forward.append({"month": forecast_month, "qty": forecast_qty})

            # Simpan ke DB
            cursor.execute("""
            INSERT INTO sales_forecast (productID, forecast_month, forecast_quantity, p, d, q, MAE)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE
                forecast_quantity=VALUES(forecast_quantity),
                p=VALUES(p), d=VALUES(d), q=VALUES(q), MAE=VALUES(MAE),
                updated_at=NOW()
            """, (product_id, forecast_month, forecast_qty, p, d, q, mae))
        db.commit()

        # Grafik
        one_year_ago = last_date - relativedelta(years=1)
        y_plot = y[y.index >= one_year_ago]

        # plt.figure(figsize=(12,6))
        # plt.plot(y_plot.index, y_plot.values, label="Data Aktual", color="black")

        # Forecast 2 bulan terakhir
        back_index = y_test_back.index

        # Forecast 2 bulan ke depan
        forward_index = [pd.to_datetime(f["month"]) for f in forecasts_forward]
        forward_values = [f["qty"] for f in forecasts_forward]

        # Gabungkan forecast 2 bulan terakhir + 2 bulan ke depan
        forecast_index = list(back_index) + forward_index
        forecast_values_all = forecast_back + forward_values
        # plt.plot(forecast_index, forecast_values_all, linestyle="--", color="red", label="Forecast")
        
        # plt.xlabel("Bulan")
        # plt.ylabel("Jumlah Penjualan")
        # plt.grid(True)
        # plt.legend()
        # plt.tight_layout()
        # plt.show()

        # Siapkan labels
        labels = list(y_plot.index.strftime("%Y-%m")) + [f["month"] for f in forecasts_forward]

        # Nilai actual
        actual = list(y_plot.values)

        # Gabungkan forecast back + forecast forward menjadi satu garis
        forecast_combined = [None]*(len(y_plot)-len(forecast_back)) + forecast_back + [f["qty"] for f in forecasts_forward]

        output = {
            "status": "success",
            "productID": product_id,
            "model": {"p": p, "d": d, "q": q, "mae": mae},
            "forecast_next_2_months": forecasts_forward,
            "forecast_last_2_months": [{"month":str(idx.date()), "qty": val} for idx,val in zip(back_index, forecast_back)],
            "chart": {
                "labels": labels,
                "actual": actual,
                "forecast": forecast_combined
            }
        }

    except Exception as e: output["message"]=str(e)
    finally:
        try: cursor.close(); db.close()
        except: pass
    print(json.dumps(output))

if __name__=="__main__":
    main()
