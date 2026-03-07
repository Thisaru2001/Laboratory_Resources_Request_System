
recapture
site key   6LcM0HMsAAAAAGiNWLW0WX5DFTSKF4F8mlQdX5SO

scret key 6LcM0HMsAAAAANzVhD2S3a9tOPDDZS0puelYCLI3

email - password- LRRS@123
     MicrobiologyLaboratorySystem@gmail.com

     LRRS System
microbiologylaboratorysystem@gmail.com


app password - cesb lydd jord elyu




On your online server you need:
CheckCommand to verifyPython installedpython --version or python3 --versionmysql-connector installedpip install mysql-connector-pythonexec() enabledCheck phpinfo() — disable_functions should NOT have exec

One thing to update before deploying — change DB config in equipment_analyzer.py:
pythonDB_CONFIG = {
    "host":     "localhost",
    "user":     "your_db_user",      # ← change
    "password": "your_db_password",  # ← change
    "database": "your_db_name",      # ← change
    "port":     3306
}
For now just keep using manual Python run on XAMPP for development, and when you deploy online it will work fully automatically! 🚀

