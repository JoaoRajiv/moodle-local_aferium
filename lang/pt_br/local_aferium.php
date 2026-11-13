<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

/**
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aferiumurl'] = 'Endereço do Aferium';
$string['aferiumurl_desc'] = 'Para onde o professor volta depois de autorizar. Altere somente se o suporte do Aferium pedir.';
$string['authorize'] = 'Autorizar';
$string['backtoaferium'] = 'Voltar ao Aferium';
$string['cachedef_codes'] = 'Códigos de conexão de uso único';
$string['connectintro'] = '<p>{$a->name}, <strong>{$a->host}</strong> está pedindo para se conectar a este Moodle com a sua conta. O Aferium poderá, somente nos seus cursos:</p>';
$string['connectreadonly'] = 'O Aferium só cria um questionário quando você pede no Aferium. Ele não altera mais nada no Moodle e nunca recebe a sua senha.';
$string['connecttitle'] = 'Conectar ao Aferium';
$string['importfailed'] = 'O Moodle não conseguiu importar as questões enviadas pelo Aferium.';
$string['pluginname'] = 'Aferium';
$string['privacy:metadata:aferium'] = 'Quando um professor se conecta, o Aferium lê dados dos cursos desse professor pelos web services do Moodle.';
$string['privacy:metadata:aferium:attempts'] = 'Tentativas dos questionários: estado, nota, resultado de cada questão e quais alternativas o aluno marcou.';
$string['privacy:metadata:aferium:email'] = 'O e-mail dos alunos matriculados, quando o professor pode vê-lo.';
$string['privacy:metadata:aferium:fullname'] = 'O nome completo dos alunos matriculados.';
$string['problem_invalidrequest'] = 'Este link de conexão não é válido. Volte ao Aferium e comece de novo.';
$string['problem_notready'] = 'Os web services estão desligados neste Moodle. Peça ao administrador para habilitar os web services e o protocolo REST.';
$string['problem_notteacher'] = 'Somente professores podem se conectar ao Aferium. A sua conta não vê relatórios de questionário em nenhum curso.';
$string['reads_attempts'] = 'ler as tentativas e os resultados dos questionários';
$string['reads_courses'] = 'ler os seus cursos e os questionários deles';
$string['reads_students'] = 'ler o nome e o e-mail dos alunos matriculados';
$string['roledescription'] = 'Permite que um professor conectado ao Aferium use o web service REST. Atribuído automaticamente pelo plugin Aferium.';
$string['writes_quiz'] = 'criar um questionário com as questões de uma avaliação, quando você pedir';
