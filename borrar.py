def corregir_datos(input_file, output_file):
    try:
        with open(input_file, 'r', encoding='utf-8') as f_in, open(output_file, 'w', encoding='utf-8') as f_out:
            for i, line in enumerate(f_in, 1):
                line = line.strip()
                if i >= 752:
                    # Procesamiento especial para líneas a partir de la línea 752
                    if line:
                        # Dividir la línea por tabulaciones
                        parts = line.split('\t', 1)
                        if len(parts) == 2:
                            phrase = parts[1].strip()
                            f_out.write(f'{phrase},\n')  # Añadir coma después de la frase
                        else:
                            print(f"Advertencia: La línea '{line}' no tiene el formato esperado.")
                else:
                    if line:  # verificar que la línea no esté vacía
                        # Separar por la primera coma para obtener id y query
                        parts = line.split(',', 1)
                        if len(parts) == 2:
                            id = parts[0].strip()
                            # Procesar la segunda parte para obtener solo la consulta SQL
                            query_part = parts[1].strip().strip('()')  # eliminar paréntesis externos
                            query_split = query_part.split(',', 1)
                            if len(query_split) > 1:
                                query = query_split[1].strip().strip('"')  # obtener lo que está a la derecha de la coma
                                # Escribir en el archivo de salida en el formato requerido con coma
                                f_out.write(f'({id},"{query}"),\n')
                            else:
                                print(f"Advertencia: La línea '{line}' no tiene el formato esperado.")
                        else:
                            print(f"Advertencia: La línea '{line}' no tiene el formato esperado.")
    except FileNotFoundError:
        print(f"No se pudo encontrar el archivo '{input_file}'.")
    except IOError:
        print("Error al leer/escribir el archivo.")

# Nombre de archivo de entrada y salida
archivo_entrada = 'datos.txt'
archivo_salida = 'datos_corregidos.txt'

# Llamar a la función para corregir los datos
corregir_datos(archivo_entrada, archivo_salida)
