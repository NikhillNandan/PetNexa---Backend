from fastapi import FastAPI, UploadFile, File, HTTPException
from tensorflow.keras.models import load_model
from PIL import Image
import numpy as np
import io

from class_names import CLASS_NAMES
from breed_info import get_breed_info

app = FastAPI(
    title="Pet Breed Classification API",
    description="Predicts dog/cat breed from an image",
    version="1.0.0"
)

MODEL_PATH = "pet_breed_model_fixed.keras"
model = load_model(MODEL_PATH)

IMG_SIZE = (224, 224)
CONFIDENCE_THRESHOLD = 0.50


def preprocess_image(image: Image.Image) -> np.ndarray:
    image = image.convert("RGB")
    image = image.resize(IMG_SIZE)
    img_array = np.array(image, dtype=np.float32) / 255.0
    img_array = np.expand_dims(img_array, axis=0)
    return img_array


def predict_breed(image: Image.Image) -> dict:
    processed_image = preprocess_image(image)

    predictions = model.predict(processed_image, verbose=0)
    class_id = int(np.argmax(predictions))
    confidence = float(np.max(predictions))

    breed = CLASS_NAMES[class_id]
    animal = "cat" if breed.startswith("cat") else "dog"

    if confidence < CONFIDENCE_THRESHOLD:
        return {
            "animal": animal,
            "breed": "uncertain",
            "confidence": round(confidence, 3),
            "message": "Breed is uncertain. Unable to predict accurately. No data available for this breed."
        }
    else:
        result = {
            "animal": animal,
            "breed": breed,
            "confidence": round(confidence, 3)
        }

    breed_data = get_breed_info(breed)
    if breed_data:
        result["breed_name"] = breed_data["breed_name"]
        result["recommended_food"] = breed_data["recommended_food"]
        result["health_care_tips"] = breed_data["health_care_tips"]
        result["dos"] = breed_data["dos"]
        result["donts"] = breed_data["donts"]
        result["lifestyle_guidance"] = breed_data["lifestyle_guidance"]

    return result


@app.get("/")
def home():
    return {
        "message": "Pet Breed Classification API is running",
        "total_classes": len(CLASS_NAMES)
    }


@app.post("/predict")
async def predict(file: UploadFile = File(...)):
    if not file.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="File must be an image")

    image_bytes = await file.read()

    try:
        image = Image.open(io.BytesIO(image_bytes))
    except Exception:
        raise HTTPException(status_code=400, detail="Invalid image file")

    result = predict_breed(image)
    return result
