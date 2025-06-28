# FastAPI Project Setup

## Setup Virtual Environment and Install Dependencies

1. **Create a virtual environment:**
bash
   python -m venv venv
Activate the virtual environment:

On Windows (PowerShell):

powershell

.\venv\Scripts\Activate.ps1
On Windows (CMD):

cmd

.\venv\Scripts\activate.bat
On macOS/Linux:

source venv/bin/activate
Install packages from requirements.txt:

pip install -r requirements.txt
uvicorn main:app --reload --port 8090
