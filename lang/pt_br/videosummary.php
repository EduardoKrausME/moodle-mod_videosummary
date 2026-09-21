<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * videosummary.php
 *
 * @package   mod_videosummary
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['actions'] = 'Ações';
$string['addreference'] = 'Adicionar referência';
$string['allowseek'] = 'Permitir avançar para partes ainda não assistidas';
$string['backtoactivity'] = 'Voltar para a atividade';
$string['backtoreport'] = 'Voltar para o relatório';
$string['charcount'] = 'Caracteres';
$string['charlimit'] = 'Máximo de caracteres';
$string['charlimit_help'] = 'Use 0 para não limitar caracteres.';
$string['charlimiterror'] = 'O resumo possui {$a->count} caracteres; o máximo é {$a->limit}.';
$string['completiondetail:percent'] = 'Assistir pelo menos {$a}% do vídeo';
$string['completiondetail:summary'] = 'Entregar o resumo final';
$string['completionpercent'] = 'Percentual mínimo assistido';
$string['completionsummary'] = 'Exigir envio final do resumo';
$string['criteriascoreinvalid'] = 'As notas dos critérios devem ficar entre 0 e 100.';
$string['criterion'] = 'Critério';
$string['defaultcriteria'] = 'Clareza
Síntese
Compreensão
Uso de evidências do vídeo';
$string['draftsaved'] = 'Rascunho salvo.';
$string['editsummary'] = 'Escrever ou editar resumo';
$string['eventcoursemoduleviewed'] = 'Video Summary visualizado';
$string['eventsummarygraded'] = 'Resumo de vídeo avaliado';
$string['eventsummarysubmitted'] = 'Resumo de vídeo entregue';
$string['feedback'] = 'Feedback';
$string['finalgrade'] = 'Nota final';
$string['fivepointserror'] = 'O resumo final deve conter pelo menos cinco pontos ou linhas distintas.';
$string['format_chapters'] = 'Resumo dividido por capítulos';
$string['format_conclusion'] = 'Uma conclusão';
$string['format_fivepoints'] = 'Cinco pontos principais';
$string['format_free'] = 'Resumo livre';
$string['format_maxwords'] = 'Resumo com limite de palavras';
$string['format_threeconcepts'] = 'Três conceitos importantes';
$string['grade'] = 'Nota';
$string['gradeaction'] = 'Avaliar';
$string['gradesaved'] = 'Avaliação salva.';
$string['grading'] = 'Avaliação';
$string['gradingcriteria'] = 'Critérios simples de avaliação';
$string['gradingcriteria_help'] = 'Informe um critério por linha. Cada critério recebe nota de 0 a 100 e a nota final é calculada pela média.';
$string['instruction_chapters'] = 'Organize a síntese por trechos ou capítulos e use referências temporais para identificá-los.';
$string['instruction_conclusion'] = 'Escreva uma conclusão que sintetize a mensagem central e suas implicações.';
$string['instruction_fivepoints'] = 'Apresente pelo menos cinco pontos principais, preferencialmente em linhas ou marcadores separados.';
$string['instruction_free'] = 'Produza uma síntese clara do vídeo com suas próprias palavras.';
$string['instruction_maxwords'] = 'Produza uma síntese respeitando o limite de palavras configurado.';
$string['instruction_threeconcepts'] = 'Apresente pelo menos três conceitos importantes em linhas ou marcadores separados.';
$string['invalidplayer'] = 'Não foi possível iniciar o player de vídeo.';
$string['invalidurl'] = 'Informe uma URL de vídeo válida.';
$string['lastposition'] = 'Última posição';
$string['maxgrade'] = 'Nota máxima';
$string['minreferences'] = 'Mínimo de referências ao vídeo';
$string['minreferences_help'] = 'Quantidade mínima de timestamps ou intervalos exigidos no envio final.';
$string['minreferenceserror'] = 'São necessárias pelo menos {$a} referência(s) ao vídeo.';
$string['modulename'] = 'Video Summary';
$string['modulenameplural'] = 'Video Summaries';
$string['no'] = 'Não';
$string['nosubmission'] = 'Nenhuma entrega de resumo foi encontrada.';
$string['notgraded'] = 'Não avaliado';
$string['pendingupdates'] = 'Algumas atualizações de acompanhamento aguardam sincronização.';
$string['percentwatched'] = 'Assistido';
$string['playbackheader'] = 'Reprodução e acompanhamento';
$string['pluginadministration'] = 'Administração do Video Summary';
$string['pluginname'] = 'Video Summary';
$string['poster'] = 'Imagem de capa';
$string['privacy:metadata:cgrades'] = 'Armazena as pontuações atribuídas aos critérios de avaliação configurados.';
$string['privacy:metadata:cgrades:criterion'] = 'O critério de avaliação.';
$string['privacy:metadata:cgrades:score'] = 'A pontuação atribuída ao critério.';
$string['privacy:metadata:progress'] = 'Armazena o progresso assistido do vídeo para cada estudante.';
$string['privacy:metadata:progress:lastposition'] = 'Última posição para retomada.';
$string['privacy:metadata:progress:percent'] = 'Percentual assistido.';
$string['privacy:metadata:progress:segments'] = 'Intervalos do vídeo assistidos.';
$string['privacy:metadata:progress:userid'] = 'Usuário cujo progresso é armazenado.';
$string['privacy:metadata:references'] = 'Armazena referências temporais ligadas ao resumo.';
$string['privacy:metadata:references:endtime'] = 'Tempo final da referência.';
$string['privacy:metadata:references:label'] = 'Título da referência.';
$string['privacy:metadata:references:note'] = 'Observação relacionada.';
$string['privacy:metadata:references:starttime'] = 'Tempo inicial da referência.';
$string['privacy:metadata:submissions'] = 'Armazena resumos, situação, notas e feedback.';
$string['privacy:metadata:submissions:feedback'] = 'Feedback do professor.';
$string['privacy:metadata:submissions:grade'] = 'Nota atribuída ao resumo.';
$string['privacy:metadata:submissions:grader'] = 'O professor que avaliou o resumo.';
$string['privacy:metadata:submissions:status'] = 'Situação de rascunho ou entrega final.';
$string['privacy:metadata:submissions:summarytext'] = 'Resumo escrito pelo usuário.';
$string['privacy:metadata:submissions:userid'] = 'Usuário que escreveu o resumo.';
$string['progress'] = 'Progresso do vídeo';
$string['reference'] = 'Referência';
$string['referencecurrent'] = 'Usar tempo atual';
$string['referenceend'] = 'Fim';
$string['referenceexample'] = 'Exemplos: 04:30 ou 01:02:15';
$string['referenceinvalid'] = 'Uma ou mais referências ao vídeo são inválidas.';
$string['referencelabel'] = 'Título ou seção';
$string['referencenote'] = 'Trecho do resumo relacionado';
$string['references'] = 'Referências ao vídeo';
$string['referencescount'] = 'Referências';
$string['referencestart'] = 'Início';
$string['removereference'] = 'Remover referência';
$string['report'] = 'Relatório do Video Summary';
$string['resumeask'] = 'Perguntar ao estudante';
$string['resumeautomatic'] = 'Retomar automaticamente';
$string['resumefromstart'] = 'Sempre iniciar do começo';
$string['resumeno'] = 'Começar novamente';
$string['resumeplayback'] = 'Retomar reprodução';
$string['resumequestion'] = 'Continuar de {$a}?';
$string['resumeyes'] = 'Continuar';
$string['savedraft'] = 'Salvar rascunho';
$string['savegrade'] = 'Salvar avaliação';
$string['score'] = 'Nota (0-100)';
$string['seekblocked'] = 'Você só pode avançar dentro das partes já assistidas.';
$string['sourceheader'] = 'Fonte do vídeo';
$string['sourcepluginmissing'] = 'A fonte de vídeo "{$a}" não está disponível.';
$string['status'] = 'Situação';
$string['status_draft'] = 'Rascunho';
$string['status_graded'] = 'Avaliado';
$string['status_notstarted'] = 'Não iniciado';
$string['status_submitted'] = 'Entregue';
$string['student'] = 'Estudante';
$string['submission'] = 'Resumo';
$string['submitfinal'] = 'Entregar resumo final';
$string['subplugintype_videosummarysource'] = 'Fonte do Video Summary';
$string['subplugintype_videosummarysource_plural'] = 'Fontes do Video Summary';
$string['summarydelivered'] = 'Resumo entregue';
$string['summaryformat'] = 'Formato do resumo';
$string['summaryinstructions'] = 'Orientações do resumo';
$string['summarylocked'] = 'Este resumo já foi entregue e não pode mais ser editado.';
$string['summarysettings'] = 'Configurações do resumo';
$string['summarystarted'] = 'Resumo iniciado';
$string['summarysubmitted'] = 'Resumo final entregue.';
$string['summarytext'] = 'Texto do resumo';
$string['threeconceptserror'] = 'O resumo final deve conter pelo menos três conceitos ou linhas distintas.';
$string['timeline'] = 'Linha do tempo assistida';
$string['timelinehint'] = 'Os trechos assistidos ficam destacados. Selecione um trecho para voltar a esse ponto.';
$string['videonotsupported'] = 'Seu navegador não consegue reproduzir esta fonte de vídeo.';
$string['videosource'] = 'Fonte do vídeo';
$string['videosummary:addinstance'] = 'Adicionar um novo Video Summary';
$string['videosummary:grade'] = 'Avaliar resumos do Video Summary';
$string['videosummary:submit'] = 'Enviar resumo de vídeo';
$string['videosummary:view'] = 'Visualizar Video Summary';
$string['videosummary:viewreport'] = 'Visualizar relatório do Video Summary';
$string['videosummaryname'] = 'Nome do Video Summary';
$string['viewaction'] = 'Visualizar';
$string['viewreport'] = 'Ver relatório';
$string['viewsubmission'] = 'Visualizar entrega';
$string['watchedpercent'] = '{$a}% assistido';
$string['wordcount'] = 'Palavras';
$string['wordlimit'] = 'Máximo de palavras';
$string['wordlimit_help'] = 'Use 0 para não limitar palavras.';
$string['wordlimiterror'] = 'O resumo possui {$a->count} palavras; o máximo é {$a->limit}.';
$string['yes'] = 'Sim';
