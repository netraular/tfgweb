# Imports the Google Cloud client library

import os
import sys
from dotenv import load_dotenv
from google.cloud import speech

# Carga las variables de entorno desde el archivo .env
load_dotenv('/var/www/html/laravel/.env')
# Ahora puedes acceder a las variables de entorno como si estuvieran definidas en el entorno de ejecución
google_credentials = os.getenv('GOOGLE_APPLICATION_CREDENTIALS')
# Configura las credenciales de la API de Google Cloud
os.environ['GOOGLE_APPLICATION_CREDENTIALS'] = google_credentials

# Verifica que se hayan pasado los argumentos correctos
if len(sys.argv) != 2:
    print("Uso: python3 googleStt.py 'path_y_nombre_del_archivo_audio'")
    sys.exit(1)

# El texto a convertir en audio y el nombre del archivo de salida se pasan como argumentos
audio_para_texto = sys.argv[1]


def run_quickstart() -> speech.RecognizeResponse:

    # Instantiates a client
    client = speech.SpeechClient()

    # The name of the audio file to transcribe
    gcs_uri = "gs://cloud-samples-data/speech/brooklyn_bridge.raw"
    file_name = "/var/www/html/laravel/storage/"+audio_para_texto
    with open(file_name, "rb") as audio_file:
        content = audio_file.read()

    audio = speech.RecognitionAudio(content=content)

    config = speech.RecognitionConfig(
        encoding=speech.RecognitionConfig.AudioEncoding.MP3,
        sample_rate_hertz=24000,
        language_code="es-ES",
    )

    # Detects speech in the audio file
    response = client.recognize(config=config, audio=audio)
    for result in response.results:
        print(f"{result.alternatives[0].transcript}")

run_quickstart()