import whisper

model = whisper.load_model("base")

# Especificar el idioma de transcripción
options = {
    "language": "es"  # Código de idioma ISO 639-1 para español
}

# Realizar la transcripción especificando el idioma
result = model.transcribe("audio2.mp3", **options)
print(result["text"])