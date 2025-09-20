import os
import numpy as np
import matplotlib.pyplot as plt

# Datos
def leer_datos(archivo):
    if not os.path.exists(archivo):
        print(f"Error: El archivo '{archivo}' no existe. Verifica la ruta.")
        return []

    with open(archivo, 'r') as f:
        pesos = f.read().split()

    return [int(peso) for peso in pesos]

pesos = leer_datos('pesos.txt')


# Definir los intervalos de amplitud 5
bins = np.arange(50, 90, 5) 

# Inicio de histograma
hist, bin_edges = np.histogram(pesos, bins=bins)

# Calculo de porcentaje de personas con peso < 65Kg
pm= sum(1 for peso in pesos if peso < 65)
porcentaje_pm = (pm / len(pesos)) * 100

# Calculo de personas con peso en el rango [70, 85)
pe = sum(1 for peso in pesos if 70 <= peso < 85)

# Mostrar resultados
print(f"Porcentaje de personas con peso menor a 65Kg: {porcentaje_pm:.2f}%")
print(f"Número de personas con peso entre 70Kg y 85Kg: {pe}")

# histograma
plt.bar(bin_edges[:-1], hist, width=4.5, align='edge', edgecolor='black', alpha=0.7)
plt.xlabel("Peso (Kg)")
plt.ylabel("Frecuencia")
plt.title("Distribución de Peso de Personas")
plt.xticks(bin_edges)
plt.grid(axis='y', linestyle='--', alpha=0.7)
plt.show()