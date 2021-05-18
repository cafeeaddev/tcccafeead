<link rel="stylesheet" type="text/css" href="./css/form.css" charset="utf-8"/>
<?php
$courseGroups3 = $DB->get_records('groups', array('courseid'=>$course->id), 'id', '*', 0, 999999);
$courseGroups2 = [];
$groups = array();
$groupsIds = '';
$coma = '';
foreach($courseGroups3 as $cg){
    $groupsIds .= $coma . ' groupid = '.$cg->id;
    $coma = ' ||';
    $courseGroups2[$cg->id] = $cg;
}

$sql = "userid = :userid AND ($groupsIds)";
$gm = $DB->get_records_select('groups_members',$sql, array('userid'=>$USER->id), 'id DESC', '*', 0, 100);

foreach($gm as $member){
    $groups[$member->groupid] = $courseGroups2[$member->groupid];
}

$postagens = $DB->get_records_list('tcccafeead_stage', 'tccid', array($tcccafeead->id), 'id', '*', 0, 999999);
$formulariosObrigatorios = $DB->get_records('tcccafeead_form', array('tccid'=>$tcccafeead->id), 'id', '*', 0, 999999);
$possuiFormularios = (count($formulariosObrigatorios) > 0)? true : false;
$formulariosObrigatoriosTipos = $DB->get_records_select('tcccafeead_form_type', '', array(), 'id', '*', 0, 999999);


$time = time();

    //disponibilidade
    $start = ($tcccafeead->timestart !== NULL && $tcccafeead->timestart !== 0)? TRUE : FALSE;
    $end = ($tcccafeead->timeend !== NULL && $tcccafeead->timeend !== 0)? TRUE : FALSE;
    $time = time();
    $be = ($time < $tcccafeead->timestart)? 'estará' : 'está';
    $be = ($time > $tcccafeead->timeend)? 'esteve': $be;
    $available = (($start && $time < $tcccafeead->timestart) || ($end && $time > $tcccafeead->timeend))?FALSE:TRUE;
    $avaClass = ($available)?'available':'unavailable';
    if($start && $end){
        $availability = "Esta tarefa $be disponível de " . date("d/m/Y", $tcccafeead->timestart) . " até " . date("d/m/Y", $tcccafeead->timeend) . ".";
    }elseif($start){
        $availability = "Esta tarefa $be disponível a partir de " . date("d/m/Y", $tcccafeead->timestart) . ".";
    }elseif($end){
        $availability = "Esta tarefa $be disponível até " . date("d/m/Y", $tcccafeead->timeend) . ".";
    }else{
        $availability = "";
    }
    if($start || $end){
        echo "<div class='$avaClass'>$availability</div>";
    }

foreach($groups as $group){
	$group->groupid = $group->id;
    $members = C_Group::getMembers($group, $coursecontext);
    $groupA = C_Group::verificaAtualizaGroupStatus($tcccafeead, $group, $postagens, $members, $CFG, $DB);
    $group = $groupA['group'];
    $tccgroup = $groupA['tccgroup'];


        if($tccgroup->status > 0){
            $status = $groupStati[$tccgroup->status];
        }else{
            $status = new stdClass();
            $status->id = 0;
            $status->name = 'pendente';
            $status->classname = 'pendente';
        }

            $chatsNaoLidos = $DB->get_records('tcccafeead_chat', array('tccid'=>$tcccafeead->id, 'groupid'=>$group->id, 'receiverid'=>$USER->id, 'timeseen'=>0), 'id', '*', 0, 999999);
            $nChatsNaoLidos = count($chatsNaoLidos);
            $chatBolha = ($nChatsNaoLidos > 0)? '<div id="cb_' . $group->id .'" class="ico16 chatBolha">' . $nChatsNaoLidos . '</div>' : '';

            ?>
            <div id="group_<?=$group->id?>" style="margin-bottom: 50px;">

                    <h1 class="groupNome">
                        <?=$group->name?>

                        <span class="chatA ico16 chatPendente"  data-groupid="<?=$group->id?>" data-groupname="<?=$group->name?>" data-institution="0" style="float:right; margin-top: 7px; height: 25px; width: 25px;"><?=$chatBolha?></span>
                    </h1>
                    <table class="boxInfo names">
                        <tr>
                            <th><?php echo get_string('teacher', 'mod_tcccafeead'); ?></th>
                            <td><?php
                             $coma = ' ';
                             foreach($members['professors'] as $profEn):
                                    $prof = $DB->get_record('user', array('id'=>$profEn->userid));

                                    ?>
                                    <?=$coma .' ' .$prof->firstname . ' ' . $prof->lastname?>

                                    <?php $coma = ',';
                                endforeach;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo get_string('status', 'mod_tcccafeead'); ?></th>
                            <td><?=$status->name?></td>
                        </tr>
                        <tr>
                            <th><?php echo get_string('members', 'mod_tcccafeead'); ?></th>
                            <td>
                                <?php
                                $coma = '';
                                foreach($members['students'] as $alunoEn):
                                    $aluno = $DB->get_record('user', array('id'=>$alunoEn->userid));
                                    $enrol = C_Group_Enrolment::verificaEnrol($aluno, $group);
                                    $estrela = ($enrol->uploader)? 'estrelaCheia':'estrelaVazia';
                                    $estrelaTitle = ($enrol->uploader)? 'pode fazer upload':'NÃO pode fazer upload';
                                    ?>
                                    <?=$coma .' ' .$aluno->firstname . ' ' . $aluno->lastname?>
                                    <span class="ico16 <?=$estrela?>" title="<?=$estrelaTitle?>"></span>

                                    <?php $coma = ',';
                                endforeach;
                                ?>
                            </td>
                        </tr>
                    </table>



                <?php
                if(count($formulariosObrigatorios) > 0){
                	echo'<div id="form_'. $group->id.'" class="form grid blocoForms box_status_'.$tccgroup->status.'_'.$group->id.'" style="">';
                	echo'<h2 class="formTitle">'.get_string("form", "mod_tcccafeead").'</h2>';
                }
                $formsAprovados = true;
                $mostrarBotaoDeEnvio = false;



				foreach($formulariosObrigatorios as $formulario){
                    $where = "tccid = :tccid and groupid = :groupid and formtypeid = :formtypeid";
                    $groupForm = $DB->get_record_select('tcccafeead_group_form', $where, array('tccid'=>$tcccafeead->id, 'groupid'=>$group->id, 'formtypeid'=>$formulario->formtypeid));
                    $formStatus = ($groupForm !== FALSE)?$DB->get_record('tcccafeead_group_form_status', array('id'=>$groupForm->status)) : 0;
                    $formStatusVal = ($groupForm !== FALSE)?$groupForm->status:0;
                    $tipo = $formulariosObrigatoriosTipos[$formulario->formtypeid];
                    $groupFormContent = ($groupForm !== FALSE) ? $groupForm->content : '' ;
                    $mostrarObs = true;
					$histEnviado = "";
					$histCorrigido = "";

					if( $groupForm == false ){
						$groupForm = new stdClass();
						$groupForm->id = '';
						$groupForm->tccid = '';
						$groupForm->groupid = '';
						$groupForm->formtypeid = '';
						$groupForm->status = '';
						$groupForm->content = '';
						$groupForm->timesubmission = '';
						$groupForm->timelaststatus = '';
						$groupForm->comentario = '';

					}

					if (  ($groupForm->timelaststatus == 0 || $groupForm->timelaststatus == '') ){
						$dataComentario = '';
					}else{
						$dataComentario = date ( "d/m/Y", $groupForm->timelaststatus ). " : ";
					}



					if( $groupForm->timesubmission != 0 && $groupForm->timesubmission != '' ){
						$histEnviado = get_string('sended', 'mod_tcccafeead').":" . date ( "d/m/Y", $groupForm->timesubmission )."<br />";
					}

					if( $groupForm->timelaststatus != 0 && $groupForm->timelaststatus != '' ){
						$histCorrigido = get_string('adjusted', 'mod_tcccafeead').": ". date ( "d/m/Y", $groupForm->timelaststatus ) ."<br />";
					}

					if( $groupForm->comentario == '' ){
						$dataComentario = '';
					}

                        if($formStatusVal !== '3'){
                            $mostrarBotaoDeEnvio = true;
                            $formsAprovados = false;
							$icon = "";
							if (  $formStatusVal ===  '4'){
								$icon = "<span class='ico32 formCancel' ></span>";
							}
                        switch($tipo->input){
                            case 'text':
                                echo '<div class="l100" style="margin: 0;">';
                                if($available){

                                    echo"<input type='text' id='f_" . $group->id . "_" . $formulario->id . "'  data-formulario='" . $formulario->id . "' value='". $groupFormContent ."'/>";
                                    echo "<label for='f_" . $group->id . "_" . $formulario->id . "'>$tipo->name $icon</label>";
									if($mostrarObs){
										echo "<div>";
										echo "$dataComentario ";
											echo $groupForm->comentario;
										echo"</div>";
									}


                                }else{
                                    echo "<h3>$tipo->name</h3><div class='formConfirmado'><p>";
                                    echo"$groupFormContent";
									echo "<div class='comentat'>";
										echo "$dataComentario ";
											echo $groupForm->comentario;
										echo"</div>";
                                    echo"</p>$icon</div><br />";
                                }
                                echo "</div>";
                                break;
                            case 'textarea':
                                echo '<div class="l100" style="margin: 0; height:114px;">';
                                if($available){
                                    echo"<textarea id='f_" . $group->id . "_" . $formulario->id . "' data-formulario='" . $formulario->id . "' >". $groupFormContent ."</textarea>";
                                    echo "<label for='f_" . $group->id . "_" . $formulario->id . "'>$tipo->name  $icon</label>";
									if($mostrarObs){
										echo "<div>";
										echo "$dataComentario ";
											echo $groupForm->comentario;
										echo"</div>";
									}

                                }else{
                                    echo "<h3>$tipo->name</h3><div class='formConfirmado'><p>";
                                    echo"$groupFormContent";
									echo "<div class='comentat'>";
										echo "$dataComentario ";
											echo $groupForm->comentario;
										echo"</div>";
                                    echo"</p>$icon</div><br />";
                                }
                                echo "</div>";
                                break;
                            case 'select':
                                $conteudo = $DB->get_records_select($tipo->axtable, '', array(), 'id', '*', 0, 999999);
                                echo '<div class="l100" style="margin: 0;">';
                                if($available){
                                    echo"<select id='f_" . $group->id . "_" . $formulario->id . "' data-formulario='" . $formulario->id . "'><option value='' style='display:nome;' class='none'> -- </option>";
                                    foreach($conteudo as $cont){
                                        $selected = ($groupForm !== false && $groupFormContent === $cont->id)? 'selected': '';
                                        echo"<option value='$cont->id' $selected>$cont->name</option>";
                                    }
                                    echo"</select>";
                                    echo "<label for='f_" . $group->id . "_" . $formulario->id . "'>$tipo->name  $icon</label>";
									if($mostrarObs){
										echo "<div class='coments'>";
										echo "$dataComentario ";
											echo $groupForm->comentario;
										echo"</div>";
									}

                                }else{
                                    echo "<h3>$tipo->name</h3><div class='formConfirmado'><p>";
                                    echo $conteudo[$groupFormContent]->name;
                                    echo"</p>$icon</div>";
                                }
                                echo "</div>";
                                break;
                        }

                    }else{

                        echo '<div class="l100" style="margin: 0;">';
                        echo "<h3>$tipo->name</h3><div class='formConfirmado'><p>";
                        switch($tipo->input){
                            case 'text':
                                echo"$groupFormContent";
                                break;
                            case 'textarea':
                                echo"$groupFormContent";
                                break;
                            case 'select':
                                $conteudo = $DB->get_record($tipo->axtable, array('id'=>$groupFormContent));
                                echo"$conteudo->name";

                                break;
                        }



                        echo"</p><span class='ico32 formConfirm' ></span></div>";
						echo "<div class='coments'>";
						echo '<h4>'.get_string('return', 'mod_tcccafeead').' em '.$dataComentario.'</h4>';
									 
											echo $groupForm->comentario;
										echo"</div>";
										echo "<div class='correcao' >";
										echo $histEnviado;
										echo $histCorrigido;
										echo "</div>";
                        echo "</div>";
                    }


                }
                    if($mostrarBotaoDeEnvio){
                        echo"<div class='l100 gh1' style='text-align: right;'><button style='min-width: 200px;' data-group='$group->id'>ENVIAR</button></div>";
                    }
                if( $tccgroup->status == 7 && $possuiFormularios){
					echo "<input class=\"btnPrint\" type=\"button\" data-groupid=\"$group->id\" value=\"Baixar\" />";
				}
                if(count($formulariosObrigatorios) > 0){?>
                </div>

                <?php }
                if($formsAprovados){
                    $i = 1;
                    $menu = array();
                    $blocos = array();
                    $inputs = array();
                    $avancar = 0;

                    $protocolos = $DB->get_records("tcccafeead_protocolo", array(
                      "userid" => $USER->id,
                      "tccid"  => $tcccafeead->id
                      )
                    );

                    foreach ($protocolos as $protocolo) {
                      if( $protocolo->acao != 'UPLOAD' ){
                        continue;
                      }
                      $numero = sprintf("%08d", $protocolo->id);
                      $arquivo = $protocolo->nome_arquivo;
                      $caminho = $protocolo->caminho_arquivo;
                      $status = $protocolo->status_acao;
                      $data =  date("d/m/Y H:i", $protocolo->timecreated);

                      switch ($status) {
                        case 1:
                          $situacao = "ARQUIVO ENVIADO";
                          break;
                          case 0:
                          $situacao = "ERRO NO ENVIO DO ARQUIVO ";
                          break;
                        default:
                          $situacao = "ARQUIVO NÃO ENVIADO";
                          break;
                      }

                      $bloco  = "<tr>";
                      $bloco .="<td class=''>$numero</td>";
                      $bloco .="<td class=''>$arquivo</td>";
                      $bloco .="<td class=''>$data</td>";
                      $bloco .="<td class=''>$situacao</td>";
                      $bloco .= "</td>";
                      $bloco .="</tr>";
                      $blocos[] = $bloco;


                    }
                    ?>
                    <h2>Protocolos</h2>
                    <table class="menuPostagens" id="tabelaProtocolos" data-groupid="<?=$group->id?>" data-avancar="<?=$avancar?>">

                            <?php foreach($blocos as $item){
                                echo $item;
                            }?>

                    </table>

                    <?php
                    $i = 1;
                    $menu = array();
                    $blocos = array();
                    $inputs = array();
                    $avancar = 0;
                    foreach($postagens as $postagem):
                        $caminhoEnvios = $CFG->dataroot .'/tcccafeead/' . $tcccafeead->id . '/' . $group->id . '/postagens/' . $i;
                        $caminhoCorrecoes = $CFG->dataroot .'/tcccafeead/' . $tcccafeead->id . '/' . $group->id . '/correcoes/' . $i;


                        $nArquivos = ($postagem->files > 1)? "devem ser postados $postagem->files arquivos": "deve ser postado $postagem->files arquivo";
                        $prazo = '';
                        $expirado = false;
                        if($postagem->timestart > 0 || $postagem->timeend > 0){
                            $prazo .= ' disponível ';
                            if($postagem->timestart > 0){
                                $prazo .= ' de ' . date("d/m/Y", $postagem->timestart);
                                $expirado = ($time > $postagem->timestart)? $expirado : true;
                            }
                            if($postagem->timeend > 0){
                                $ultimoSegundo = strtotime(date('Y-m-d 23:59:59', $postagem->timeend));
                                $prazo .= ' até ' . date("d/m/Y", $ultimoSegundo);
                                $expirado = ($time < $ultimoSegundo)? $expirado : true;
                            }
                        }
                        $prazo .= ($expirado)? ' (encerrado)': '';
                        $podeDeletarPostagem = false;
                        if( $tccgroup->status == 3 ){
                          $podeDeletarPostagem = true;
                        }
                        $dirCorrecoes = C_Arquivos::mostrarPasta($caminhoCorrecoes,$tcccafeead->id,$group->id, $i, 'c', '', 'correcoes');

                        if( $dirCorrecoes['html'] != ""){
                          $podeDeletarPostagem = false;
                        }

                        $dirPostagens = C_Arquivos::mostrarPasta($caminhoEnvios,$tcccafeead->id,$group->id, $i, 'e', '', 'postagens', $podeDeletarPostagem);

                        $prazo = ($expirado && $dirPostagens['nArquivos'] < $postagem->files)? ", <span class='expiradoPendente'>$prazo</span>" : ", $prazo";
                        $bloco  = '<td id="postagem_' . $group->id .'_' . $i .'" class="postagem" data-limiteenvios="' . $postagem->files . '">';
                        $bloco .=   '<div class="postagemInfo"><p>' . $nArquivos . $prazo . '</p></div>';
                        $bloco .=   '<div class="pasta">';
                        $bloco .=       '<p>'.get_string('adjusteds', 'mod_tcccafeead').'</p>';
                        $bloco .=       '<span id="cArquivos_' . $group->id . '_' . $i .'_correcoes">';
                        $bloco .=           $dirCorrecoes['html'];
                        $bloco .=       '</span>';
                        $bloco .=   '</div>';
                        $bloco .=   '<div class="pasta">';
                        $bloco .=   '<p>'.get_string('sends', 'mod_tcccafeead').'</p>';
                        $bloco .=   '<span id="cArquivos_' . $group->id . '_' . $i .'_postagens">';
                        $bloco .=       $dirPostagens['html'];
                        $bloco .=   '</span>';
                        $bloco .=   '</div>';
                        $bloco .= '</td>';

                        $blocos[] = $bloco;
                        if($dirPostagens['nArquivos'] >= $postagem->files || $expirado){
                            $avancar++;
                        }
                        if($dirPostagens['nArquivos'] < $postagem->files && $expirado === false && $formsAprovados && $available){
                            $inputs[] = "<td><input type='file' class='upload' data-maxfile='".$postagem->files."' id='iF_$group->id" . "_" . "$i' data-groupid='$group->id' data-stage = '$i' data-role='aluno' multiple /><label id='iFL_$group->id" . "_". "$i' for='iF_$group->id" . "_" . "$i' style='float:right; margin: 5px;'></label></td>";
                        }
                        else{
                            $inputs[] = '<td></td>';
                        }
                        $class = ($i === 1)? 'sSelecionado' : '';
                        $menu[] = "<td id='menuItem_$group->id" . '_' . $i . "' class='$class' data-group='$group->id' data-stage='$i'>$postagem->name</td>";

                    $i++;
                    endforeach;
                    ?>

                    <h2><?php echo get_string('post', 'mod_tcccafeead'); ?></h2>
                    <table class="menuPostagens" data-groupid="<?=$group->id?>" data-avancar="<?=$avancar?>">
                        <tr>
                            <?php foreach($menu as $item){
                                echo $item;
                            }?>
                        </tr>
                    </table>
                    <div class="boxPostagens">
                        <div id="deslizante_<?=$group->id?>" class="deslizante" style="width:<?=count($postagens)*100?>%;">
                            <table >
                                <tr>
                                <?php foreach($blocos as $bloco){
                                    echo $bloco;
                                }?>
                                </tr>
                                <tr>
                                <?php foreach($inputs as $input){
                                    echo "$input";
                                }?>
                                </tr>
                            </table>
                        </div>
                    </div><br><br>

                <?php
                }

            if($tcccafeead->importfrom !== '0'):
                    $tccImport = $DB->get_record('tcccafeead', array('id'=>$tcccafeead->importfrom));
                    $courseImport = $DB->get_record('course', array('id'=>$tccImport->course));
                    $postagensImport = $DB->get_records_list('tcccafeead_stage', 'tccid', array($tccImport->id), 'id', '*', 0, 999999);
                    $groupsImport = ($group->idnumber != '')? $DB->get_records('groups', array('idnumber'=>$group->idnumber, 'courseid'=>$courseImport->id)): false;
                    $i = 1;
                    $menu = array();
                    $blocos = array();
                    $inputs = array();

                foreach($postagensImport as $postagem):
                    $nArquivos = ($postagem->files > 1)? "devem ser postados $postagem->files arquivos": "deve ser postado $postagem->files arquivo";

                    $dirPostagens = array();
                    $dirCorrecoes = array();
                    foreach($groupsImport as $groupImport){
                        $caminhoEnvios = $CFG->dataroot .'/tcccafeead/' . $tccImport->id . '/' . $groupImport->id . '/postagens/' . $i;
                        $caminhoCorrecoes = $CFG->dataroot .'/tcccafeead/' . $tccImport->id . '/' . $groupImport->id . '/correcoes/' . $i;
                        $dirPostagens[]= C_Arquivos::mostrarPasta($caminhoEnvios,$tccImport->id,$groupImport->id, $i, 'e', '', 'postagens');
                        $dirCorrecoes[]= C_Arquivos::mostrarPasta($caminhoCorrecoes,$tccImport->id,$groupImport->id, $i, 'c', '', 'correcoes');
                    }

                    $bloco  = '<td id="postagem_' . $group->id .'_' . $i .'" class="postagem" data-limiteenvios="' . $postagem->files . '">';
                    $bloco .=   '<div class="postagemInfo"></div>';
                    $bloco .=   '<div class="pasta">';
                    $bloco .=       '<p>'.get_string('adjuteds', 'mod_tcccafeead').'</p>';
                    $bloco .=       '<span id="cArquivos_' . $group->id . '_' . $i .'_correcoes">';
                    foreach ($dirCorrecoes as $dirCorrecao){
                        $bloco .=           $dirCorrecao['html'];
                    }
                    $bloco .=       '</span>';
                    $bloco .=   '</div>';
                    $bloco .=   '<div class="pasta">';
                    $bloco .=   '<p>'.get_string('sends', 'mod_tcccafeead').'</p>';
                    $bloco .=   '<span id="cArquivos_' . $group->id . '_' . $i .'_postagens">';
                    foreach ($dirPostagens as $dirPostagem){
                    $bloco .=       $dirPostagem['html'];
                    }
                    $bloco .=   '</span>';
                    $bloco .=   '</div>';
                    $bloco .= '</td>';

                    $blocos[] = $bloco;
                    $class = ($i === 1)? 'sSelecionado' : '';
                    $menu[] = "<td id='menuItemH_$group->id" . '_' . $i . "' class='$class' data-group='$group->id' data-stage='$i'>$postagem->name</td>";


                $i++;
                endforeach;
                ?>
                <h2><?php echo get_string('inherited_files', 'mod_tcccafeead'); ?></h2><br>
                <?php echo get_string('course', 'mod_tcccafeead'); ?>: <b><a href="<?=$CFG->wwwroot?>/course/view.php?id=<?=$courseImport->id?>"><?=$courseImport->fullname?></a></b><br>
                <?php echo get_string('activity', 'mod_tcccafeead'); ?>: <b><a href="<?=$CFG->wwwroot?>/mod/tcccafeead/view.php?id=<?=$courseModuleImport->id?>&p=envio"><?=$tccImport->name?></a></b><br>
                <table class="menuHerdados" data-groupid="<?=$group->id?>">
                    <tr>
                        <?php foreach($menu as $item){
                            echo $item;
                        }?>
                    </tr>
                </table>
                <div class="boxHerdados" style="display:block;">
                    <div id="deslizanteH_<?=$group->id?>" class="deslizante" style="width:<?=count($postagensImport)*100?>%;">
                        <table >
                            <tr>
                            <?php foreach($blocos as $bloco){
                                echo $bloco;
                            }?>
                            </tr>

                        </table>
                    </div>
                </div>

                <?php endif; ?>
                </div>

<?php   ?>
<div id="boxNotas">
<h2><?php echo get_string('grade', 'mod_tcccafeead'); ?></h2><br>
<?php
if($tcccafeead->stagegrade === '0'){
	$notaTC = $DB->get_record('tcccafeead_grade', array('groupid'=>$group->id, 'tccid'=>$tcccafeead->id, 'type'=>1));
	$notaT = (($notaTC == false || $notaTC == null) || $notaTC->value === null)? '' : $notaTC->value;
	$disableNotaT = ($tccgroup->status === '7')? ' disabled' : '';
}else{
	$notasStage = $DB->get_records('tcccafeead_stage_grade', array('groupid'=>$group->id, 'tccid'=>$tcccafeead->id));
	if(count($notasStage) > 0){
	$somaNotasT = 0;
	foreach($notasStage as $ns){
	$somaNotasT += $ns->value;
	}
	$media = round($somaNotasT ,2);
	$notaT = ($somaNotasT > 0)? $media : '';

	}else{
	$notaT = '';
	}
	$disableNotaT = ' disabled';
}
$disableNotaA =($tccgroup->status === '7')? ' disabled' : '';

$notaAC = $DB->get_record('tcccafeead_grade', array('groupid'=>$group->id,'tccid'=>$tcccafeead->id, 'type'=>2));
if($notaAC !== FALSE ){
	$notaA = ($notaAC->value === null)? '' : $notaAC->value ;
}else{
	$notaA = '';
}
 							if( $tcccafeead->levelid == "2" ){
                            	$media =($notaT !== '' && $notaA  !== '')? round(($notaT + $notaA) / 2, 2) : '';
                            }
                            if( $tcccafeead->levelid == "1" ){
                            	$media =($notaT !== '' && $notaA  !== '')? round(($notaT + $notaA), 2) : '';
                            }
?>
<div class="caixaDeNotas">
<label><?php echo get_string('works', 'mod_tcccafeead'); ?><?=($tcccafeead->stagegrade === '1')?' (soma das postagens)': ''?>:</label>
	<?php if($notaT && $tccgroup->status === 7){ ?>
		<input style="background: transparent none;box-shadow: none;border: medium none;" type="text" id = "nt_<?=$group->id?>" class="notaGrupo apenasNumeros" value="<?=$notaT?>" data-type="1" data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->id?>" disabled/>
	<?php }else{ ?>
		<input style="background: transparent none;box-shadow: none;border: medium none;" type="text" id = "nt_<?=$group->id?>" class="notaGrupo apenasNumeros" value="nenhuma nota" data-type="1" data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->id?>" disabled/>
	<?php } ?>
</div>
<?php if($tcccafeead->banca === '1'): ?>
<div class="caixaDeNotas">
<label><?php echo get_string('presentation', 'mod_tcccafeead'); ?>:</label></label>
<?php if($notaA && $tccgroup->status === 7){ ?>
	<input style="background: transparent none;box-shadow: none;border: medium none;" type="text" id = "na_<?=$group->id?>" class="notaGrupo apenasNumeros" value="<?=$notaA?>" data-type="2" data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->id?>" disabled/>
<?php }else{ ?>
	<input style="background: transparent none;box-shadow: none;border: medium none;" type="text" id = "na_<?=$group->id?>" class="notaGrupo apenasNumeros" value="nenhuma nota" data-type="2" data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->id?>" disabled/>
<?php } ?>
</div>
<div class="caixaDeNotas">

</div>
<div class="caixaDeNotas">
<label><?php echo get_string('final_grade', 'mod_tcccafeead'); ?>: </label>
<?php if($media && $tccgroup->status === 7){ ?>
	<input style="background: transparent none;box-shadow: none;border: medium none;" type="text" id = "nm_<?=$group->id?>" class="apenasNumeros" value="<?=$media?>" disabled/>
<?php }else{ ?>
	<input style="background: transparent none;box-shadow: none;border: medium none;" type="text" id = "nm_<?=$group->id?>" class="apenasNumeros" value="nenhuma nota" disabled/>
<?php } ?>

</div>
<?php endif; ?>
</div>
<br>
<br>

       <?php
            if($tcccafeead->importfromassign !== '0' && $tcccafeead->importfromassign !== '' && $tcccafeead->importfromassign !== NULL){
                $files = C_Arquivos::listarArquivosTarefa($tcccafeead, $group);
                $assign = $DB->get_record('assign', array('id'=>$tcccafeead->importfromassign));
                $impCourse = ($assign !== FALSE)? $DB->get_record('course', array('id'=>$assign->course)): false;
                $impCourseId =($assign !== FALSE)? $impCourse->id : '';
                $assname = ($assign !== FALSE)? $impCourse->fullname . ' - ' . $assign->name :'Atividade não encontrada' ;
                $courseModuleImport =($assign !== FALSE)? $DB->get_record('course_modules', array('course'=>$assign->course, 'module'=>1, 'instance'=>$tcccafeead->importfromassign)): FALSE;
                $courseLink = ($courseModuleImport !== FALSE)? "<b><a href='". $CFG->wwwroot."/course/view.php?id=". $impCourseId ."'>$assname</a></b>" : 'não encontrado';
                $assignLink = ($courseModuleImport !== FALSE)? "<b><a href='" . $CFG->wwwroot ."/mod/assign/view.php?id=" . $courseModuleImport->id ."'>".$assign->name ."</a></b>" : 'não encontrado';
                echo"<h2>".get_string('inherited_files_assign', 'mod_tcccafeead')."</h2>"
                        . "<br>"
                       .get_string('course', 'mod_tcccafeead') . ": $courseLink<br>"
                       .get_string('activity', 'mod_tcccafeead') . ": $assignLink<br><br>"
                        . "<div class='boxHerdadosTarefa'>";
                foreach ($files as $file) {

                    $filename = $file->filename;
                    if($filename !== '.' && $filename !== '..'){
                        $url = $CFG->wwwroot.'/pluginfile.php/' . $file->contextid .'/assignsubmission_file/submission_files/' . $file->itemid .'/' .$file->filename;
                        echo"<a href='$url' download>$filename</a><br>";
                    }
                }
                echo"</div>";
            }

}
?>

<script>

								$(function(){


									$('.btnPrint').click(function() {

										 event.preventDefault()

										var mywindow = window.open('', 'PRINT', 'height=400,width=600');

										var style = "<style> @media print {";
										    style += "button {";
											style += "display: none !important;";
											style += "}";
										    style += "input,";
										    style += "textarea {";
											style += "border: none !important;";
											style += "box-shadow: none !important;";
											style += "outline: none !important;";
											style += " resize: none;";
											style += "}";
											style += "}";
											style += "</style>";
										mywindow.document.write( style );
										mywindow.document.write( document.querySelector('.box_status_7_'+$(this).data("groupid")).innerHTML );

										// necessary for IE >= 10
										mywindow.focus(); // necessary for IE >= 10*/

										mywindow.print();


										return false;
									});

								});


							</script>
