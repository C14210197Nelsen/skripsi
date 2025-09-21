import sys, json, mysql.connector, pandas as pd

# DB Config
DB_CONFIG = dict(
    host="localhost",
    user="root",
    password="",
    database="gwanglobaldigital"
)

def main():
    output = {"status": "error", "message": ""}
    try:
        if len(sys.argv) <= 1:
            raise Exception("Product ID diperlukan")
        product_id = int(sys.argv[1])

        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor(dictionary=True)

        # Ambil model terakhir
        cursor.execute("""
            SELECT p, d, q, MAE
            FROM sales_forecast
            WHERE productID = %s AND forecast_quantity IS NULL
            ORDER BY updated_at DESC LIMIT 1
        """, (product_id,))
        model_row = cursor.fetchone()
        if not model_row:
            raise Exception("Belum ada model yang disimpan.")

        # Ambil forecast terakhir (2 bulan terakhir + 2 bulan ke depan)
        cursor.execute("""
            SELECT forecast_month AS bulan, forecast_quantity AS qty
            FROM sales_forecast
            WHERE productID = %s AND forecast_quantity IS NOT NULL
            ORDER BY forecast_month DESC
            LIMIT 4
        """, (product_id,))
        forecast_rows = cursor.fetchall()

        if not forecast_rows:
            raise Exception("Belum ada hasil forecasting.")

        # Pisahkan backward dan forward
        forecast_rows = sorted(forecast_rows, key=lambda r: r["bulan"])
        df = pd.DataFrame(forecast_rows)

        # backward = data yang sudah lewat (qty hasil forecast_back)
        forecast_last = df.iloc[:-2].to_dict(orient="records") if len(df) > 2 else []
        # forward = 2 bulan ke depan
        forecast_next = df.iloc[-2:].to_dict(orient="records")

        # Output
        output = {
            "status": "success",
            "productID": product_id,
            "model": model_row,
            "forecast_last_2_months": forecast_last,
            "forecast_next_2_months": forecast_next,
            "chart": {
                "labels": list(df["bulan"]),
                "actual": [],     # kalau mau bisa tambahkan query actual sales juga
                "forecast": list(df["qty"])
            }
        }

    except Exception as e:
        output["message"] = str(e)
    finally:
        try:
            cursor.close()
            db.close()
        except:
            pass

    print(json.dumps(output, default=str))


if __name__ == "__main__":
    main()
