<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                                         программа автопарковщик
//
// 1. По алгоритму A* находим кратчайшие пути от каждого авто до каждой парковки
// 2. Находим минимальное количество соотношений вариантов одному авто - одна парковка
// 3. Последовательно проходим все варианты, фиксируя количество пройденных шагов и успешно припаркованных авто
// 3.1 Перед очередным шагом просчитываем, какое авто лучше пустить вперед
// 4. Выбираем лучший вариант и отображаем его на экране
//
// 2012
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


?>
<!DOCTYPE html>
<html>
<head>
<title>Автопарковка</title>
<meta charset="utf-8" />
<meta name="author" content="Макаренок Роман ha-cehe@rambler.ru" />
<style type="text/css">
    body {
    font-size:30px;
    }
    div { 
    font-family:courier new;
    line-height: 30px; 
    }
    p {
    font-family:courier new;
    line-height: 5px;
    } 
    p.del {
    font-size: 20px;
    }
</style>
</head>
<body>
<!--
<form  action="" method="post" enctype="multipart/form-data">
	<input type="file" name="matrix" size="88" />
	<input type="submit"  name="load" value="Загрузить файл" />
</form>
-->
<?php

// ===================================== загрузка матрицы ================================================================
/*
if (!empty($_POST['load'])) {
    $src = $_FILES["matrix"]['tmp_name'];
    $file = $_FILES["matrix"]['name'];
    if (!empty($file)) {
        if (is_uploaded_file($src)) {
            $dest = $_SERVER['DOCUMENT_ROOT'] . "/parking.txt";
            copy($src, $dest);
        }
    }
    header('Location: ' . $_SERVER[PHP_SELF]);
    die();
}
*/
// ==================================== Блок функций ====================================================================

function a_star($start, $target, $neighbors, $heuristic, $car_name)
{
    $open_heap = array($start); // binary min-heap of indexes with values in $f
    $open = array($start => true); // set of indexes
    $closed = array(); // set of indexes

    $g[$start] = 0;
    $h[$start] = $heuristic($start, $target);
    $f[$start] = $g[$start] + $h[$start];

    while ($open) {

        $i = heap_pop($open_heap, $f);
        unset($open[$i]);
        $closed[$i] = true;

        if ($i == $target) {
            $path = array();
            for (; $i != $start; $i = $from[$i])
                $path[] = $i;

            return array_reverse($path);
        }

        foreach ($neighbors($i, $car_name) as $j => $step)
            if (!array_key_exists($j, $closed))
                if (!array_key_exists($j, $open) || $g[$i] + $step < $g[$j]) {
                    $g[$j] = $g[$i] + $step;
                    $h[$j] = $heuristic($j, $target);
                    $f[$j] = $g[$j] + $h[$j];
                    $from[$j] = $i;

                    if (!array_key_exists($j, $open)) {
                        $open[$j] = true;
                        heap_push($open_heap, $f, $j);
                    } else {
                        heap_raise($open_heap, $f, $j);
                    }
                }

    }

    return false;
}
function heap_float(&$heap, &$values, $i, $index)
{
    for (; $i; $i = $j) {
        $j = ($i + $i % 2) / 2 - 1;
        if ($values[$heap[$j]] < $values[$index])
            break;
        $heap[$i] = $heap[$j];
    }
    $heap[$i] = $index;
}

function heap_push(&$heap, &$values, $index)
{
    heap_float($heap, $values, count($heap), $index);
}

function heap_raise(&$heap, &$values, $index)
{
    heap_float($heap, $values, array_search($index, $heap), $index);
}

function heap_pop(&$heap, &$values)
{
    $front = $heap[0];
    $index = array_pop($heap);
    $n = count($heap);
    if ($n) {
        for ($i = 0;; $i = $j) {
            $j = $i * 2 + 1;
            if ($j >= $n)
                break;
            if ($j + 1 < $n && $values[$heap[$j + 1]] < $values[$heap[$j]])
                ++$j;
            if ($values[$index] < $values[$heap[$j]])
                break;
            $heap[$i] = $heap[$j];
        }
        $heap[$i] = $index;
    }
    return $front;
}
function node($x, $y)
{
    global $width;
    return $y * $width + $x;
}

function coord($i)
{
    global $width;
    $x = $i % $width;
    $y = ($i - $x) / $width;
    return array($x, $y);
}

function neighbors($i, $car_name)
{
    global $map, $width, $height;
    list($x, $y) = coord($i);
    $neighbors = array();

    if ($x - 1 >= 0 && (($map[$y][$x - 1] == '0') || (preg_match("/[a-zA-Z]/", $map[$y][$x -
        1]) == true)))
        $neighbors[node($x - 1, $y)] = 1;
    if ($x + 1 < $width && (($map[$y][$x + 1] == '0') || (preg_match("/[a-zA-Z]/", $map[$y][$x +
        1]) == true)))
        $neighbors[node($x + 1, $y)] = 1;
    if ($y - 1 >= 0 && (($map[$y - 1][$x] == '0') || (preg_match("/[a-zA-Z]/", $map[$y -
        1][$x]) == true)))
        $neighbors[node($x, $y - 1)] = 1;
    if ($y + 1 < $height && (($map[$y + 1][$x] == '0') || (preg_match("/[a-zA-Z]/",
        $map[$y + 1][$x]) == true)))
        $neighbors[node($x, $y + 1)] = 1;

    return $neighbors;
}

function heuristic($i, $j)
{
    list($i_x, $i_y) = coord($i);
    list($j_x, $j_y) = coord($j);
    return abs($i_x - $j_x) + abs($i_y - $j_y);
}


function display_way($map)
{

    $display = '';
    foreach ($map as $line) {
        $display .= $line . '<br>';
    }
    return ($display);
}
function cnt_way($map, $car_name)
{
    $cnt = 0;
    foreach ($map as $line) {
        $line_str = str_split($line);
        foreach ($line_str as $line_one) {
            if ($line_one == $car_name) {
                $cnt++;
            }
        }
    }
    return ($cnt);
}

// =====================================  обработка матрицы ==============================================================

if (!defined('I'))
    define('I', 10000);

$str = array();
$cars = array();
$parcs = array();
$mem = array();
$way = array();
$lazy = '';
if (!file_exists("parking.txt")) {
    echo 'Файл parking.txt не найден';
    die();
}
$file = file('parking.txt');
foreach ($file as $line => $f) {
    if ($line == 0) {
        $z = explode(' ', $f);
        $m = $z[0];
        $n = $z[1];
        if ($m <= 5) {
            echo 'Ошибка, M меньше заданного числа<br>';
            break;
        }
        if ($n > 100) {
            echo 'Ошибка, N больше заданного числа';
            break;
        }
    } else {

        $f = trim(str_replace(array(
            "\r\n",
            "\r",
            "\n",
            "\n\r",
            "'",
            '"'), "", $f));
        $string = str_split($f);

        $str[$line - 1] = $string;
        // определяем местоположение парковок и машин
        foreach ($str[$line - 1] as $x => $y) {

            if (preg_match("/[a-z]/", $y) == true) { // авто
                $lazy .= '<span style="color:red">' . $y . '</span>';
                $cars[] = array($line - 1, $x);
                $car_name[] = $y;
                $str[$line - 1][$x] = $y;
            } else
                if (preg_match("/[A-Z]/", $y) == true) { // стоянка
                    $lazy .= '<span style="color:blue">' . $y . '</span>';
                    $parcs[] = array($line - 1, $x);
                    $parc_name[] = $y;
                    $str[$line - 1][$x] = $y;
                } else
                    if ($y === '0') { // дорога
                        $lazy .= '<span style="color:green">' . $y . '</span>';
                        $way[] = array($line - 1, $x);
                        $str[$line - 1][$x] = '0';
                    } else
                        if ($y == 1) {
                            $lazy .= $y;
                            $str[$line - 1][$x] = '1';
                        }

        }
        $string_again = implode($str[$line - 1]);
        $mem[$line - 1] = $string_again;
        $lazy .= '<br>';
    }

}


echo '<div>' . $lazy . '</div>';

$width = $m;
$height = $n;

$map = $mem;

$node_car = array();
$paths = array();
$cnt = array();
$parcing_car = array();
$parcing_all = array();
$car_point = array();
$parc_point = array();

// 1. находим все возможные пути от каждой машины к каждой парковке

foreach ($cars as $c => $car) {

    foreach ($parcs as $p => $parc) {

        $start = node($car[1], $car[0]); // авто
        $target = node($parc[1], $parc[0]);
        $node_car[$c] = $start;
        $path = a_star($start, $target, 'neighbors', 'heuristic', '');
        if (is_array($path)) {
            $paths[$c][$p] = $path;

            array_unshift($path, $start);

            foreach ($path as $i) {
                list($x, $y) = coord($i);
                $map[$y][$x] = $car_name[$c];
            }

            $cnt[$c][$p] = cnt_way($map, $car_name[$c]);
            $parcing_car[] = $c;
            $parcing_all[] = $p;
            $car_point[$c][$p] = array($car[1], $car[0]);
            $parc_point[$c][$p] = array($parc[1], $parc[0]);
            $map = $mem;
        }
    }
}

// 2. соотносим свободные парковки с авто

$nodes_cars = count($cars);
$nodes_parcs = count($parcs);
$dist = $cnt;
$marsh = array();
$all_marsh = array();
global $steps;
$useful_m = I;

for ($k = 0; $k < count($parcing_car); $k++) { // все парковки
    $marsh[$parcing_car[$k]] = $parcing_all[$k];

    for ($i = 0; $i < $nodes_cars; $i++) { // все авто
        for ($j = 0; $j < count($dist[$i]); $j++) { // все парковки этого авто
            if (count($marsh) < $nodes_cars && !array_key_exists($i, $marsh) && !in_array($j,
                $marsh)) {
                $marsh[$i] = $j;
                ksort($marsh);
            }
        }
    }
    if (count($marsh) == $nodes_cars) {
        if ($all_marsh) {
            $check = 0;
            foreach ($all_marsh as $am => $amvalue) {
                unset($g);
                $g = array_diff_assoc($all_marsh[$am], $marsh); // находим и не включаем одинаковые пути
                if (empty($g)) {
                    $check = 1;
                }
            }
            if ($check == 0) {
                $all_marsh[] = $marsh;
            }
        } else {
            $all_marsh[] = $marsh;
        }
    }
    unset($marsh);
}

// 3. Последовательно проходим все варианты

$winner_m = array();

if ($all_marsh) {

    for ($m = 0; $m < count($all_marsh); $m++) {

        $map = $mem; // восстанавливаем картину
        $pat = array();

        $steps = 0;

        for ($i = 0; $i < count($paths); $i++) {
            for ($j = 0; $j < count($paths[$i]); $j++) {

                if ($all_marsh[$m][$i] == $j) {
                    $step = count($paths[$i][$j]);
                    if ($step > $steps) {
                        $steps = $step;
                    }
                    $pat[$i][$j] = $paths[$i][$j]; // задействованные шаги
                }
            }
        }

        // обрабатываем сетку вывода
        $stp = 0;
        $useful = 0;
        $print = array();
        $useful_car = array();
        for ($k = 0; $k < $steps; $k++) {
            $change = 0;

            // 3.1 определяем очередность продвижения. первыми будут двигаться авто, перед которыми дорога

            $new_nodes_cars = array();
            foreach ($pat as $ps => $patcar) {
                foreach ($patcar as $pc => $patparc) {
                    foreach ($patparc as $pp => $patstep) {
                        if ($pp == $k) {

                            list($x, $y) = coord($patstep);
                            if ($map[$y][$x] === '0' || (preg_match("/[A-Z]/", $map[$y][$x]) == true)) {
                                $new_nodes_cars[] = $ps;
                            }
                        }
                    }
                }
            }
            foreach ($pat as $ps => $patcar) {
                if ($new_nodes_cars) {
                    if (!in_array($ps, $new_nodes_cars)) {
                        $new_nodes_cars[] = $ps;
                    }
                }
            }

            if (!$new_nodes_cars)
                $new_nodes_cars = array_keys($cars);

            foreach ($new_nodes_cars as $bc => $b) {

                if ($pat[$b]) {
                    foreach ($pat[$b] as $arrd => $arr_d) {
                        foreach ($pat[$b][$arrd] as $pp => $ppp) {
                            if ($pp == $k) {
                                $h = $b;
                                $d = $arrd;
                                break (2);
                            }
                        }
                    }
                } else {
                    continue;
                }
                if (in_array($b, $useful_car))
                    continue;

                $strt = node($cars[$b][1], $cars[$b][0]); // авто
                $trgt = node($parc_point[$b][$d][0], $parc_point[$b][$d][1]);
                list($x, $y) = coord($pat[$h][$d][$k]);

                if ((($map[$y][$x] == '0') || (preg_match("/[A-Z]/", $map[$y][$x]) == true)) &&
                    $map[$y][$x] != '#') {
                    $name = '';
                    for ($pi = 0; $pi < $k; $pi++) {
                        if (!empty($parc_zone[$h][$pi])) { // возвращаем имя парковки на место, едем дальше
                            list($x, $y) = coord($parc_zone[$h][$pi]);
                            if (isset($parc_zone_name[$h][$name])) {
                                $map[$y][$x] = $parc_zone_name[$h][$name];
                                unset($parc_zone[$h][$pi]);
                                unset($parc_zone_name[$h][$name]);
                            }
                        }
                    }

                    if ($k > 0 && $pat[$h][$d][$k - 1]) { // изменяем точку на карте
                        list($x, $y) = coord($pat[$h][$d][$k - 1]);
                        if (((preg_match("/[A-Z]/", $map[$y][$x]) != true))) {
                            $map[$y][$x] = '0';
                        }
                        if ($pat[$h][$d][$k] == $pat[$h][$d][$k - 1]) {
                            list($x, $y) = coord($strt);
                            $map[$y][$x] = '0';
                        }
                    } else {
                        list($x, $y) = coord($strt);
                        if ($map[$y][$x] == $car_name[$b]) {
                            $map[$y][$x] = '0';
                            $change = 1;
                        }
                    }

                    list($x, $y) = coord($pat[$h][$d][$k]);
                    if (preg_match("/[A-Z]/", $map[$y][$x]) == true) {

                        // авто наезжает на парковку, но едет дальше, к своему месту. запоминаем
                        $parc_zone[$h][$k] = $pat[$h][$d][$k];
                        $parc_zone_name[$h][$name] = $map[$y][$x];
                        $map[$y][$x] = $car_name[$b];
                        $change = 1;

                    } else {
                        $map[$y][$x] = $car_name[$b];
                        $change = 1;
                    }

                    if ($pat[$h][$d][$k] == $trgt) { // авто на своем месте
                        $map[$y][$x] = '#';
                        $useful_car[] = $b;
                        $change = 1;
                        $useful++;
                    }

                } elseif ((preg_match("/[a-z]/", $map[$y][$x]) == true)) { // пропускаем авто

                    for ($ii = count($pat[$h][$d]) - 1; $ii >= 0; $ii--) {

                        $pat[$h][$d][$ii + 1] = $pat[$h][$d][$ii];

                    }
                    $steps++;

                }

            } // end cycle new_nodes_cars

            if (isset($pat[$h][$d][$k])) { // becose first position is 0 and it can be too
                $stp++;
                $display = display_way($map);
                $print[$m][] = '<div>' . $display . '</div>';

            }

            // выбираем лучший вариант
            if ($useful == $nodes_cars) { // все авто на парковках
                if ($stp < $useful_m) { // забираем только лучший вариант

                    $useful_m = $stp;
                    $winner = $m;
                    $winner_m = $print[$m];
                }
            }

            if ($change == 0) {
                break; // прерываем цикл на случай неразрешимой пробки
            }

        } // end cycle steps
    } // end cycle all_marsh
} // end if all_marsh

// 4. вывод на экран результата

if ($useful_m == I) {
    echo '<p>Подходящих решений не найдено</p>';
} else {
    echo '<p>' . $useful_m . '</p>';
    if ($winner_m) {
        for ($i = 0; $i < count($winner_m); $i++) {
            echo $winner_m[$i];
            if ($i < count($winner_m) - 1) {
                echo '<p class="del">---------</p>';
            }
        }
    }
}
?>
</body>
</html>