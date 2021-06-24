nota1 = float(input('Digite a 1a nota: '))
peso1 = float(input('Digite o peso da 1a nota: '))
nota2 = float(input('Digite a 2a nota: '))
peso2 = float(input('Digite o peso da 2a nota: '))

mediaponderada = ((nota1 * peso1) + (nota2 * peso2)) / (peso1 + peso2)

print('A média ponderada é:', mediaponderada)

input('Tecle <enter> para continuar.')
