import json
import os
import sys
import warnings

import cv2
import joblib
import numpy as np

os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'
warnings.filterwarnings("ignore")
os.environ['TF_ENABLE_ONEDNN_OPTS'] = '0'

def main():
    input_json = sys.stdin.read()
    input_data = json.loads(input_json)
    image_url = input_data.get('image_url', '')

    if not image_url:
        print(json.dumps({"error": "No image URL provided."}))
        return

    # For simplicity, assume image_url is a local path or download it. But since IVR, perhaps it's not feasible.
    # For now, treat as local path.
    image_path = image_url  # Assuming it's a path, but in reality, need to download if URL.
    
    try:
        if not os.path.exists(image_path):
            print(json.dumps({"error": f"Image file does not exist at path: {image_path}"}))
            return
        if not os.access(image_path, os.R_OK):
            print(json.dumps({"error": f"Image file is not readable: {image_path}"}))
            return
        image = cv2.imread(image_path)
        if image is None:
            print(json.dumps({"error": f"Could not load image. File may be corrupted or unsupported format: {image_path}"}))
            return
        if image.size == 0:
            print(json.dumps({"error": "Loaded image has zero size"}))
            return
        image_resized = cv2.resize(image, (32, 32))
        image_normalized = image_resized / 255.0
        image_batch = np.expand_dims(image_normalized, axis=0)
        base_dir = os.path.dirname(os.path.abspath(__file__))
        model_path = os.path.join(base_dir, "model", "image_fire_predictor.pkl")
        if not os.path.exists(model_path):
            print(json.dumps({"error": f"Model file not found at path: {model_path}"}))
            return
            
        model = joblib.load(model_path)
        prediction = model.predict(image_batch, verbose=0)
        probability = float(prediction[0][0]) if isinstance(prediction, np.ndarray) else float(prediction[0])
        fire_risk = 1 if probability > 0.5 else 0
        output = {"fire_risk": int(fire_risk), "probability": probability}
        print(json.dumps(output))
        
    except Exception as e:
        print(json.dumps({"error": f"Exception occurred: {str(e)}"}))

if __name__ == "__main__":
    main()
