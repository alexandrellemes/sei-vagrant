print('Iniciando no Python')
Num1 = int(input('Digite um número:'))
Num2 = int(input('Digite outro número:'))
print('O resultado de', Num1, '+', Num2, 'é:', Num1 + Num2)
print('Fim do programa')

print('Número: ',type(Num1))
print('Número: ',type(Num2))

import math

print(math.sqrt(36))

print(math.factorial(5))

// Tipo tuple -- imutável
meses = ('janeiro','fevereiro','marco','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro')
print(type(meses))

// Tipo list - mutável
alunos = ['joao', 'maria', 'pedro', 'helena']
print(type(alunos))

alunos[0]

alunos[1] = 'mariah'

alunos.append('Ricardo')

alunos.insert(1,'Paula')

alunos.sort()

alunos.pop(1) // retira o aluno.
alunos.remove('pedro')

alunos2 = ['joana', 'jorge']

total = alunos + alunos2
