parking
=======

Программа реализует следующий функционал:  
В городе, представленным прямоугольным полем из MxN клеток (5 < M,N <=100), каждая клетка представляет собой дорогу, тротуар, автомобиль или въезд на подземную парковку (0 – это дорога, 1 – тротуар, a-z – это автомобили, A-Z – это парковочные места, # - парковочное место, занятое автомобилем).  Программу управляет автомобилями, чтобы как можно быстрее припарковать все автомобили на парковки.  
Программа написана на языке PHP и выполняется в виде web-скрипта.  
Хранение входных данных организовано в файле.  
Выходные данные выводятся на экран браузера.  
Уточнения  
- автомобиль занимает ровно одну клетку и за один шаг либо остается на месте, либо передвигается в одну из четырех соседних клеток (соседними считаются клетки с общими гранями). Клетка, в которую, передвигается автомобиль, должна быть дорогой и должна быть свободна перед перемещением.
- если в первоначальной конфигурации в клетке располагается автомобиль, значит в этой клетке находится дорога.
- два автомобиля не могут находиться одновременно в одной клетке, даже если движутся во встречных направлениях
- когда автомобиль перемещается на клетку с парковкой, значение в этой клетке устанавливается в #. Далее на эту клетку автомобили заезжать не могут.
- гарантируется, что у каждого автомобиля есть путь к стоянке.
- реализована возможность проезда через пустую парковку.

Входные данные  
Входные данные находятся в локальном файле parking.txt в котором в первой строке находится размер города, два числа M и N разделенных пробелами.  
В следующих N строках находится описание начальной конфигурации города.  
Выходные данные  
Первой строкой выводится количество шагов T необходимых для парковки всех автомобилей, далее в Т блоках по N+1 строчек выводятся состояния города после очередного шага i
(0<i<=T). Каждый блок содержит текущее состояние города и строку разделитель.  
Пример теста  
6 5  
111111  
1A00t1  
111101  
11B0x1  
111111  
Пример ответа  
3  
111111  
1A0t01  
111101  
11Bx01  
111111
- -----  
111111  
1At001  
111101  
11#001  
111111  
- -----  
111111  
1#0001  
111101  
11#001  
111111  

[Demo: http://свадебный-красноярск.рф/parking.php](http://свадебный-красноярск.рф/parking.php)

