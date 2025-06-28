
# {
#   "distance_from_futsal": 3.2,
#   "player_level": 4,
#   "day_of_visit": 5,
#   "occupation": "student",
#   "gender": "male",
#   "age_group": 3
# }

# log_distance = log1p(distance_from_futsal)

# interaction_feature = distance_from_futsal * player_level

# day_sin = sin(2π × day_of_visit / 7)

# is_weekend = 1 if day_of_visit in [5, 6] else 0

# {
#   "log_distance": 1.435,
#   "interaction_feature": 12.8,
#   "day_of_visit": 5,
#   "is_weekend": 1,
#   "day_sin": 0.78,
#   "player_level": 4,
#   "occupation": "student",
#   "gender": "male",
#   "age_group": 3
# }

# [
#   {
#     "feature": "distance_from_futsal",
#     "type": "float",
#     "valid_range": "0.0 – 20.0+",
#     "description": "Distance (in km) from the user's location to the futsal facility. Should be non-negative."
#   },
#   {
#     "feature": "player_level",
#     "type": "int",
#     "valid_range": "1 – 5",
#     "description": "Skill level or experience level of the player. Higher means more advanced."
#   },
#   {
#     "feature": "day_of_visit",
#     "type": "int",
#     "valid_range": "0 – 6",
#     "description": "Day of the week: 0=Monday, 6=Sunday. Check if it's a weekend (5=Saturday, 6=Sunday)."
#   },
#   {
#     "feature": "occupation",
#     "type": "str",
#     "valid_range": "e.g., \"student\", \"employee\", \"unemployed\"",
#     "description": "Category used for target encoding. Should match training categories."
#   },
#   {
#     "feature": "gender",
#     "type": "str",
#     "valid_range": "\"male\", \"female\" (or other strings)",
#     "description": "Used for one-hot encoding. Must match known training values."
#   },
#   {
#     "feature": "age_group",
#     "type": "int",
#     "valid_range": "1 – 3",
#     "description": "Grouped age category. Values above 3 were grouped during training (4, 5 → 3)."
#   }
# ]

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import joblib
import pandas as pd

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class BookingFeatures(BaseModel):
    log_distance: float
    interaction_feature: float
    day_of_visit: int
    is_weekend: int
    day_sin: float
    player_level: int
    occupation: str
    gender: str
    age_group: int

try:
    model_pipeline = joblib.load('./futsal.joblib')
except Exception as e:
    raise RuntimeError(f"Failed to load model: {e}")

@app.get("/")
def root():
    return {"message": "Futsal prediction API is up. Use /docs for Swagger UI."}
occupation_mapping = {
    "student": 0,
    "employee": 1,
    "unemployed": 2,
}

gender_mapping = {
    "male": 0,
    "female": 1,
}

@app.post("/predict_booking", tags=["Prediction"])
def predict_booking(features: BookingFeatures):
    input_data = features.dict()

    # Map categorical features to numeric
    input_data['occupation'] = occupation_mapping.get(input_data['occupation'].lower(), -1)
    input_data['gender'] = gender_mapping.get(input_data['gender'].lower(), -1)

    # Check for unknown categories
    if input_data['occupation'] == -1 or input_data['gender'] == -1:
        raise HTTPException(status_code=400, detail="Unknown category in occupation or gender")

    input_df = pd.DataFrame([input_data])

    try:
        prediction = model_pipeline.predict(input_df)
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Prediction error: {e}")

    return {"prediction": int(prediction[0])}
