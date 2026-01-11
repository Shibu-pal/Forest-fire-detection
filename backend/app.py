from fastapi import FastAPI, UploadFile, File, HTTPException, BackgroundTasks
from pydantic import BaseModel
import joblib
import pandas as pd
import numpy as np
import cv2
import os

app = FastAPI()

# Load models ONCE at startup to avoid "cold start" delays
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
model = joblib.load(os.path.join(BASE_DIR, "model", "fire_predictor.pkl"))
feature_columns = joblib.load(os.path.join(BASE_DIR, "model", "feature_columns.pkl"))
image_model = joblib.load(os.path.join(BASE_DIR, "model", "image_fire_predictor.pkl"))

class PredictionData(BaseModel):
    temperature: float
    humidity: float
    wind_speed: float
    vegetation_type: str
    elevation: float

@app.post("/predict/data")
async def predict_data(data: PredictionData):
    input_df = pd.DataFrame([data.dict()])
    
    # Process vegetation type similar to predict_fire.py
    vegetation_encoded = pd.get_dummies(input_df['vegetation_type'], prefix='vegetation_type')
    input_df = input_df.drop(columns=['vegetation_type']).join(vegetation_encoded)

    for col in feature_columns:
        if col not in input_df.columns:
            input_df[col] = 0

    processed = input_df[feature_columns]
    prob = float(model.predict_proba(processed)[0][1])
    return {"fire_risk": 1 if prob > 0.5 else 0, "probability": prob}

def remove_file(path: str):
    """Helper function to delete the file from disk."""
    try:
        if os.path.exists(path):
            os.remove(path)
    except Exception as e:
        print(f"Error deleting file {path}: {e}")

@app.post("/predict/image")
async def predict_image(background_tasks: BackgroundTasks, file: UploadFile = File(...)):
    # Create a temporary path to save the upload for processing
    temp_path = f"temp_{file.filename}"
    with open(temp_path, "wb") as buffer:
        buffer.write(await file.read())

    try:
        # 1. Read and process the image
        image = cv2.imread(temp_path)
        if image is None:
            raise HTTPException(status_code=400, detail="Invalid image format")

        image_resized = cv2.resize(image, (32, 32)) / 255.0
        image_batch = np.expand_dims(image_resized, axis=0)
        
        # 2. Run prediction
        prediction = image_model.predict(image_batch, verbose=0)
        probability = float(prediction[0][0])
        fire_risk = 1 if probability > 0.5 else 0
        
        # 3. Add the deletion task to background tasks
        background_tasks.add_task(remove_file, temp_path)
        
        return {"fire_risk": int(fire_risk), "probability": probability}

    except Exception as e:
        # Ensure cleanup even if prediction fails
        background_tasks.add_task(remove_file, temp_path)
        raise HTTPException(status_code=500, detail=str(e))