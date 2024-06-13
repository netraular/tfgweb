import os
import sys
import asyncio
from ollama import AsyncClient
from ollama import Client

# Verifica que se hayan pasado los argumentos correctos
if len(sys.argv) != 2:
    print("Uso: python3 llama3api.py 'texto'")
    sys.exit(1)
# El texto a convertir en audio y el nombre del archivo de salida se pasan como argumentos
question = sys.argv[1]

client = Client(host='http://localhost:11434')
response = client.chat(model='codeqwen7bToSql', messages=[
  {
    'role': 'user',
    'content': question,
    'keep_alive':-1,
  },
])

print(response["message"]["content"])