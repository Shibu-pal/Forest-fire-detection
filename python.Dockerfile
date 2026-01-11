FROM python:3.10-slim

WORKDIR /app

# Install system dependencies for OpenCV
RUN apt-get update && apt-get install -y \
    libgl1-mesa-glx \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements and install
COPY backend/requirement.txt .
RUN pip install --no-cache-dir -r requirement.txt fastapi uvicorn python-multipart

# Copy the backend code (including your models)
COPY backend /app

# Run the persistent API
CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8000"]