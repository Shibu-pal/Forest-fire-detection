import json
import sys
import os

import joblib
import pandas as pd


def main():
    input_json = sys.stdin.read()
    input_data = json.loads(input_json)

    base_dir = os.path.dirname(os.path.abspath(__file__))
    model_path = os.path.join(base_dir, "model", "fire_predictor.pkl")
    feature_columns_path = os.path.join(base_dir, "model", "feature_columns.pkl")

    model = joblib.load(model_path)
    feature_columns = joblib.load(feature_columns_path)

    input_df = pd.DataFrame([input_data])
    vegetation_encoded = pd.get_dummies(input_df['vegetation_type'], prefix='vegetation_type')
    input_df = input_df.drop(columns=['vegetation_type'])
    input_df = pd.concat([input_df, vegetation_encoded], axis=1)

    for col in feature_columns:
        if col not in input_df.columns:
            input_df[col] = 0

    processed = input_df[feature_columns]
    prediction = model.predict(processed)[0]

    output = {"fire_risk": int(prediction)}
    print(json.dumps(output))

if __name__ == "__main__":
    main()
