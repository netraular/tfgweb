
# Imports the Google Cloud client library

from google.cloud import speech

def run_quickstart() -> speech.RecognizeResponse:

    # Instantiates a client
    client = speech.SpeechClient()

    # The name of the audio file to transcribe
    gcs_uri = "gs://cloud-samples-data/speech/brooklyn_bridge.raw"

    file_name = "output.flac"
    with open(file_name, "rb") as audio_file:
        content = audio_file.read()

    audio = speech.RecognitionAudio(content=content)

    config = speech.RecognitionConfig(
        encoding=speech.RecognitionConfig.AudioEncoding.FLAC,
        sample_rate_hertz=24000,
        language_code="en-US",
    )

    # Detects speech in the audio file
    response = client.recognize(config=config, audio=audio)
    for result in response.results:
        print(f"Transcript: {result.alternatives[0].transcript}")

run_quickstart()