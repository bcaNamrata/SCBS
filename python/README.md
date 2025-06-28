# FastAPI Project Setup

## Setup Virtual Environment and Install Dependencies

1. **Create a virtual environment:**

1. cd python
   bash
   python -m venv venv
   Activate the virtual environment:

On Windows (PowerShell):

powershell
2...
.\venv\Scripts\Activate.ps1
(venv) PS C:\xampp\htdocs\SCBS\python>
On Windows (CMD):

cmd

.\venv\Scripts\activate.bat
On macOS/Linux:

source venv/bin/activate
Install packages from requirements.txt:

pip install -r requirements.txt 3.
uvicorn main:app --reload --port 8090
