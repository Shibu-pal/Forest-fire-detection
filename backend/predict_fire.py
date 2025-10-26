import json
import os
import sys
import warnings
import joblib
import pandas as pd

os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'
warnings.filterwarnings("ignore")
os.environ['TF_ENABLE_ONEDNN_OPTS'] = '0'

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
    probabilities = model.predict_proba(processed)[0]
    probability = float(probabilities[1])  # Probability of fire (class 1)
    fire_risk = 1 if probability > 0.5 else 0

    output = {"fire_risk": int(fire_risk), "probability": probability}
    print(json.dumps(output))

if __name__ == "__main__":
    main()
