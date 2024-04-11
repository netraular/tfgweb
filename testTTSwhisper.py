import whisper

model = whisper.load_model("small")

# Especificar el idioma de transcripción
options = {
    "language": "es"  # Código de idioma ISO 639-1 para español
}

# Opcional: Below is an example usage of whisper.detect_language() and whisper.decode() which provide lower-level access to the model.
# # load audio and pad/trim it to fit 30 seconds
# audio = whisper.load_audio("audio.mp3")
# audio = whisper.pad_or_trim(audio)

# # make log-Mel spectrogram and move to the same device as the model
# mel = whisper.log_mel_spectrogram(audio).to(model.device)

# # detect the spoken language
# _, probs = model.detect_language(mel)
# print(f"Detected language: {max(probs, key=probs.get)}")

# # decode the audio
# options = whisper.DecodingOptions()
# result = whisper.decode(model, mel, options)


# Realizar la transcripción especificando el idioma
result = model.transcribe("20240304100339-1605.mp3", **options)
print(result["text"])