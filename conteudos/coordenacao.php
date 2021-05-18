<?php

// $groups = $DB->get_records('groups', array('courseid'=>$course->id), 'id', '*', 0, 999999);
//$grupoSelect = "SELECT DISTINCT g.* FROM {groups} g JOIN {groups_members} gm ON g.id = gm.groupid WHERE g.courseid = ? GROUP BY g.id HAVING COUNT(*) >= 2 order by g.id";
//$groups = $DB->get_records_sql ( $grupoSelect, array ($course->id ) );

$postagens = $DB->get_records_list ( 'tcccafeead_stage', 'tccid', array ($tcccafeead->id
), 'id', '*', 0, 999999 );
$formulariosObrigatorios = $DB->get_records_list ( 'tcccafeead_form', 'tccid', array ($tcccafeead->id
), 'id', '*', 0, 999999 );
$possuiFormularios = (count ( $formulariosObrigatorios ) > 0) ? true : false;
$formulariosObrigatoriosTipos = $DB->get_records_select ( 'tcccafeead_form_type', '', array (), 'id', '*', 0, 999999 );
$hash = hash('sha256', 'coordenacao.php Alterado em 18/09/2018 18:00');



// disponibilidade
$start = ($tcccafeead->timestart !== NULL && $tcccafeead->timestart !== 0) ? TRUE : FALSE;
$end = ($tcccafeead->timeend !== NULL && $tcccafeead->timeend !== 0) ? TRUE : FALSE;
$time = time ();
$be = ($time < $tcccafeead->timestart) ? get_string('willbe', 'mod_tcccafeead') : get_string('it_is', 'mod_tcccafeead');
$be = ($time > $tcccafeead->timeend) ? get_string('been', 'mod_tcccafeead') : $be;
$available = (($start && $time < $tcccafeead->timestart) || ($end && $time > $tcccafeead->timeend)) ? FALSE : TRUE;
$avaClass = ($available) ? 'available' : 'unavailable';
if ($start && $end) {
	$availability = get_string('assign', 'mod_tcccafeead')." $be ".get_string('availablefrom', 'mod_tcccafeead')." " . date ( "d/m/Y", $tcccafeead->timestart ) .' ' .get_string('until', 'mod_tcccafeead') .' '. date ( "d/m/Y", $tcccafeead->timeend ) . ".";
} elseif ($start) {
	$availability = get_string('assign', 'mod_tcccafeead')." $be ".get_string('availablefrom', 'mod_tcccafeead'). " ". date ( "d/m/Y", $tcccafeead->timestart ) . ".";
} elseif ($end) {
	$availability = get_string('assign', 'mod_tcccafeead')." $be ".get_string('availablefrom', 'mod_tcccafeead')." " . date ( "d/m/Y", $tcccafeead->timeend ) . ".";
} else {
	$availability = "";
}
if ($start || $end) {
	echo "<div class='$avaClass'>$availability</div>";
}
$mostrarTema = false;
?>
<!-- HASH DO ARQUIVO: <?=$hash;?> -->
<h2><?php echo get_string('groups', 'mod_tcccafeead'); ?></h2>
<div class="titleObs"><?php echo get_string('showing', 'mod_tcccafeead'); ?>< <span><?=($pageStart==0)?1:$pageStart?></span> a <span><?=($pageEnd<=$totalGeral)?$pageEnd:$totalGeral?></span></div>
<div class="listaGrupos">
	<table id="cabecalhoLista">
		<tr>
			<th></th>
			<th><?php echo get_string('groups', 'mod_tcccafeead'); ?></th>
			<th><?php echo get_string('teacher', 'mod_tcccafeead'); ?></th>
			<th><?php echo get_string('students', 'mod_tcccafeead'); ?></th>
			<th><?php echo get_string('status', 'mod_tcccafeead'); ?></th>
			<th></th>
		</tr>
		<tr class="filtros">
			<th></th>
			<th><input type="text" id="fNomeGrupo" value="" /></th>
			<th><input type="text" id="fNomeProfessor" value="" /></th>
			<th><input type="text" id="fNomeAluno" value="" /></th>
			<th><select id="fStatus">
					<option value=""><?php echo get_string('all', 'mod_tcccafeead'); ?></option>
					<option value="0"><?php echo get_string('pendent', 'mod_tcccafeead'); ?></option>
                    <?php foreach($groupStati as $status): ?>
                    <option value="<?=$status->id?>"><?=$status->name?></option>
                    <?php endforeach; ?>
                </select></th>
			<th></th>
		</tr>
		<tr class="filtros">
			<th colspan="6"><button id="filtrar"><?php echo get_string('filter', 'mod_tcccafeead'); ?></button></th>
		</tr>

	</table>
	<table class="paginacao">
		<?php
			for($i=0; $i < $totalPaginas; $i++){
				?>
				<td class="<?=($pageNumber == $i)?'current':'pagina'?>" > <a href="./view.php?id=<?=$courseModule->id?>&page=<?=$i?>" ><?=($i+1)?></a> </td>
				<?php

			}
		?>
	</table>
        <?php
								// percorrendo todos grupos deste curso
								foreach ( $groups as $group ) :

									try {
										$group->groupid = $group->id;
										$members = C_Group::getMembers($group, $coursecontext);
										$groupA = C_Group::verificaAtualizaGroupStatus ( $tcccafeead, $group, $postagens, $members );
										$group = $groupA ['group'];
										$tccgroup = $groupA ['tccgroup'];

										if( $statusGrupo != "" && $statusGrupo != $tccgroup->status ){
											continue;
										}

										if ($tccgroup->status > 0) {
											$status = $groupStati [$tccgroup->status];
										} else {
											$status = new stdClass ();
											$status->id = 0;
											$status->name = get_string('pendent', 'mod_tcccafeead');
											$status->classname = 'pendente';
										}
										$chatsNaoLidos = $DB->get_records ( 'tcccafeead_chat', array ('tccid' => $tcccafeead->id,'groupid' => $group->id,'receiverid' => $USER->id,'timeseen' => 0
										), 'id', '*', 0, 999999 );
										$nChatsNaoLidos = count ( $chatsNaoLidos );
										$chatBolha = ($nChatsNaoLidos > 0) ? '<div id="cb_' . $group->id . '" class="chatBolha">' . $nChatsNaoLidos . '</div>' : '';
										if( ($tcccafeead->banca == '1') && ($tccgroup->status == '6' || $tccgroup->status == '7') ){
											$mostrarTema = true;
										}else if( ($tcccafeead->banca == '0') && count($postagens) > 0 ){
											$mostrarTema = true;
										}else{
											$mostrarTema = false;
										}
										?>
	<div id="groupContainer_<?=$group->id?>"
		class="groupContainer <?=$status->classname?>"
		data-classname="<?=$status->classname?>" data-group="<?=$group->id?>"
		data-status="<?=$tccgroup->status?>" data-nome="<?=$group->name?>">

		<table class="groupContainer_header" data-group="<?=$group->groupid?>">

			<tr>
				<td></td>
				<td class="nomegrupo" id="nomeGrupo_<?=$group->id?>"><?=$group->name?></td>
				<td class="profnome" id="profNome_<?=$group->id?>">
                        <?php
										$coma = '';
										foreach ( $members ['professors'] as $profEn ) :
											$prof = $DB->get_record ( 'user', array ('id' => $profEn->userid
											) );
											// echo $coma .'<a download href="{$CFG->wwwroot}/user/view.php?id={$prof->id}">' . $prof->firstname . ' ' . $prof->lastname .'</a>';
											echo $coma . $prof->firstname . ' ' . $prof->lastname;
											$coma = ', ';
										endforeach
										;
										?>
                        </td>
				<td class="countmember"><?=count($members['students'])?></td>
				<td title="<?=''?>" id="status_<?=$group->id?>"
					class="s_<?=$status->id?>" data-groupid="<?=$group->id?>" ><?=$status->name?></td>
				<td class="chaticon" data-groupid="<?=$group->id?>"
					data-groupname="<?=$group->name?>" data-institution="1"><span
					class="ico16 chatPendente"><?=$chatBolha?></span></td>
			</tr>
		</table>
		<div id="groupDetalhes_<?=$group->id?>" class="groupDetalhes">
			<div class="subCabecalho fechado" data-block="alunos_<?=$group->id?>">
				<?php echo get_string('students', 'mod_tcccafeead'); ?>
				<div class="botoesAlunos"></div>
			</div>
			<div id="alunos_<?=$group->id?>" style="display: none;">
				<table class="subTabela">
					<tr>
						<th style="text-align: left;"><?php echo get_string('students', 'mod_tcccafeead'); ?></th>
						<th style="text-align: right;"><?php echo get_string('permissionupload', 'mod_tcccafeead'); ?></th>
					</tr>
                        <?php
										foreach ( $members ['students'] as $alunoEn ) :
											$aluno = $DB->get_record ( 'user', array ('id' => $alunoEn->userid
											) );
											$enrol = C_Group_Enrolment::verificaEnrol ( $aluno, $group );
											$estrela = ($enrol->uploader) ? 'estrelaCheia' : 'estrelaVazia';
											$estrelaTitle = ($enrol->uploader) ? 'pode fazer upload' : 'NÃO pode fazer upload';
											?>
                                <tr>
						<td><span id="uNome_<?=$enrol->id?>"><a 
								href="<?=$CFG->wwwroot?>/user/view.php?id=<?=$aluno->id?>"><?=$aluno->firstname . ' ' . $aluno->lastname?></a></span>
						</td>
						<td><span id="botUp_<?=$enrol->id?>" class="ico16 <?=$estrela?>16"
							data-enrolmentid="<?=$enrol->id?>" title="<?=$estrelaTitle?>"></span>
						</td>

					</tr>
                                <?php
										endforeach
										;
										?>
                         </table>
			</div>
			<!-- FIM DIV ALUNOS -->
                    <?php
										if ($possuiFormularios) :
											?>
            <div id="boxFormularios" class="box_status_<?=$tccgroup->status?>_<?=$group->id?>">
				<div class="subCabecalho aberto " data-block="formulario_<?=$group->id?>"><?php echo get_string('form', 'mod_tcccafeead'); ?></div>
				<div id="formulario_<?=$group->id?>" class="boxContent"
					style="display: none;">
                            <?php
											$mostrarBotoesForm = '';
											$disableEdits = '';
											if( $tccgroup->status == '7' ){
												$mostrarBotoesForm = 'finalizado esconde';
												$disableEdits = 'finalizado desabilita';
											}
											foreach ( $formulariosObrigatorios as $form ) {
												$where = "tccid = :tccid and groupid = :groupid and formtypeid = :formtypeid";
												$groupForm = $DB->get_record_select ( 'tcccafeead_group_form', $where, array ('tccid' => $tcccafeead->id,'groupid' => $group->id,'formtypeid' => $form->formtypeid
												) );
												$tipo = $formulariosObrigatoriosTipos [$form->formtypeid];
												if ($groupForm != false && isset ( $groupForm ) ) {

													switch ($tipo->input) {
														case 'text' :
															$conteudo = htmlspecialchars($groupForm->content);
															break;
														case 'textarea' :
															$conteudo = htmlspecialchars($groupForm->content);
															break;
														case 'select' :
															$oConteudo = $DB->get_record ( $tipo->axtable, array ('id' => $groupForm->content
															) );

															if( $oConteudo != null && $oConteudo != false ){
																$conteudo = $oConteudo->name;
															}else{
																$conteudo = "";
															}
															break;
													}
													switch($groupForm->status){
														case 0 :
															$icoConfirm = '';
															$icoNegar = '';
															break;
														case 1 :
															$icoConfirm = 'icoformConfirmMorto';
															$icoNegar = 'icoformCancelaMorto';
															break;
														case 2 :
															$icoConfirm = 'icoformConfirmMorto';
															$icoNegar = 'icoformCancelaMorto';
															break;
														case 3 :
															$icoConfirm = 'icoformConfirm';
															$icoNegar = 'icoformCancelaMorto';
															break;
														case 4 :
															$icoConfirm = 'icoformConfirmMorto';
															$icoNegar = 'icoformCancela';
															break;
														default :
															$icoConfirm = '';
															$icoNegar = '';
                                            }
                                        }else{
                                            $conteudo = '';
                                            $icoConfirm = '';
											$icoNegar = '';
                                        }
												?>

                                <div class="contentttt">    <?=$formulariosObrigatoriosTipos[$form->formtypeid]->name?>:<br>
					<div class="forms conteudo">
						<div class="formConfirm">
							<span id="bot_<?=$group->id?>_<?=$form->formtypeid?>"
								class="ico32 <?=$icoConfirm?>" data-per='coord'
								data-group="<?=$group->groupid?>"
								data-formtype="<?=$form->formtypeid?>"></span>
						</div>

						<?php

												?>
												<div class="formConfirm"><span id="bot_nao_<?=$group->id?>_<?=$form->formtypeid?>" class="ico32 <?=$icoNegar?> <?=$mostrarBotoesForm?>" data-per = 'coord' data-group="<?=$group->id?>" data-formtype="<?=$form->formtypeid?>"></span></div>
												<?php

											?>
						<!-- FIM DIV formConfirm -->
                                       <?=$conteudo?> <br />
                                        </div>

										<div class="forms correcao">
											<?php
												if( $groupForm != false && $groupForm->timesubmission != 0 && $groupForm->timesubmission != '' ){
											?>
											<?php echo get_string('sended', 'mod_tcccafeead'); ?> <?=date ( "d/m/Y", $groupForm->timesubmission )?> <br />
											<?php }?>
											<?php
												if( $groupForm != false && $groupForm->timelaststatus != 0 && $groupForm->timelaststatus != '' ){
											?>
											<?php echo get_string('adjusted', 'mod_tcccafeead'); ?> <?=date ( "d/m/Y", $groupForm->timelaststatus )?> <br />
											<?php }?>
										</div>
					<?php echo get_string('comentsabout', 'mod_tcccafeead'); ?> <?=$formulariosObrigatoriosTipos[$form->formtypeid]->name?>:<br>
					<div class="forms comentario">
					<?php
						if( $groupForm != false ){
							?>
							<textarea id="comentario_<?=$group->id?>_<?=$form->formtypeid?>" data-formulario="<?=$form->formtypeid?>" data-per = 'coord' data-group="<?=$group->id?>" data-formtype="<?=$form->formtypeid?>" data-old="<?=$groupForm->comentario?>" class="txtComentario"><?=$groupForm->comentario?></textarea>
							<?php
						} else {
							?>
							<textarea id="comentario_<?=$group->id?>_<?=$form->formtypeid?>" data-formulario="<?=$form->formtypeid?>" data-per = 'coord' data-group="<?=$group->id?>" data-formtype="<?=$form->formtypeid?>" data-old="" class="txtComentario"></textarea>
							<?php
						}
					?>



					</div></div>
					<!-- FIM DIV forms -->
                                    <?php
											}
											?>
											<?php if($tccgroup->status == 7 && $possuiFormularios ) { ?>


											<button class="btnPrint" data-groupid="<?=$group->id?>"><?php echo get_string('download', 'mod_tcccafeead'); ?></button>

											<?php } ?>
                 </div>
				<!-- FIM DIV FORM GRUPO -->
			</div>
			<!-- FIM DIV BOX FORMULARIOS -->


										<?php endif;
										// SE ESSE CURSO É CONTINUAÇÃO DE OUTRO, ABRE-SE A ABA DE ARQUIVOS HERDADOS

										if ($tcccafeead->importfrom !== '0') :
											$tccImport = $DB->get_record ( 'tcccafeead', array ('id' => $tcccafeead->importfrom
											) );
											$courseImport = $DB->get_record ( 'course', array ('id' => $tccImport->course
											) );
											$postagensImport = $DB->get_records_list ( 'tcccafeead_stage', 'tccid', array ($tccImport->id
											), 'id', '*', 0, 999999 );
											$groupsImport = ($group->idnumber != '') ? $DB->get_records ( 'groups', array ('idnumber' => $group->idnumber,'courseid' => $courseImport->id
											) ) : FALSE;

											?>

                    <div id="boxArquivosHerdados">
				<div class="subCabecalho fechado"
					data-block="herdados_<?=$group->id?>"><?php echo get_string('inherited_files', 'mod_tcccafeead'); ?></div>
				<div id="herdados_<?=$group->id?>" class="boxContent"
					style="display: none;">
					<p style="text-align: center; font-weight: bold;"><?=$courseImport->fullname . ' - ' . $tccImport->name?></p>
                        <?php
											$i = 1;
											foreach ( $postagensImport as $postagem ) :

												$dirPostagens = array ();
												$dirCorrecoes = array ();
												foreach ( $groupsImport as $groupImport ) {
													$caminhoEnvios = $CFG->dataroot . '/tcccafeead/' . $tccImport->id . '/' . $groupImport->id . '/postagens/' . $i;
													$caminhoCorrecoes = $CFG->dataroot . '/tcccafeead/' . $tccImport->id . '/' . $groupImport->id . '/correcoes/' . $i;
													$dirPostagens [] = C_Arquivos::mostrarPasta ( $caminhoEnvios, $tccImport->id, $groupImport->id, $i, 'e', '', 'postagens', false, true );

													$dirCorrecoes [] = C_Arquivos::mostrarPasta ( $caminhoCorrecoes, $tccImport->id, $groupImport->id, $i, 'c', '', 'correcoes' );
												}
												?>

                            <div class="postagem"
						data-groupid="<?=$group->id?>" data-stage="<?=$i?>"
						data-limiteenvios="<?=$postagem->files?>">

						<p style="display: block; clear: both;"><?=$postagem->name?></p>

                                <?php foreach($dirPostagens as $dirPostagem):?>
                                    <div style="display: inline-block;">
							<p>envios</p>
							<span id="cArquivos_<?=$group->id . '_' . $i .'_postagens'?>">
                                            <?=$dirPostagem['html']?>
                                        </span>
						</div><?php endforeach;?>
                                <?php foreach($dirCorrecoes as $dirCorrecao):?><div
							style="display: inline-block; float: right; clear: both;">
							<p>correções</p>
							<span id="cArquivos_<?=$group->id . '_' . $i .'_correcoes'?>">
                                            <?=$dirCorrecao['html']?>
                                        </span>
						</div>
                                <?php endforeach;?>


                            </div>
                        <?php
												$i ++;
											endforeach
											;
											?>
                        </div>
				<div style="display: block; clear: both;"></div>

			</div>











										<?php endif;
										if ($tcccafeead->importfromassign !== '0' && $tcccafeead->importfromassign !== '' && $tcccafeead->importfromassign !== NULL) :
											$files = C_Arquivos::listarArquivosTarefa ( $tcccafeead, $group );
											$assign = $DB->get_record ( 'assign', array ('id' => $tcccafeead->importfromassign
											) );
											$impCourse = ($assign !== FALSE) ? $DB->get_record ( 'course', array ('id' => $assign->course
											) ) : false;
											$assname = ($assign !== FALSE) ? $impCourse->fullname . ' - ' . $assign->name : 'Atividade não encontrada';
											?>
                            <div id="boxArquivosHerdadosTarefa">
				<div class="subCabecalho fechado"
					data-block="herdadostarefa_<?=$group->id?>"><?php echo get_string('inherited_files_assign', 'mod_tcccafeead'); ?></div>
				<div id="herdadostarefa_<?=$group->id?>" class="boxContent"
					style="display: none;">
					<p style="text-align: center; font-weight: bold;"><?=$assname?></p>
                                    <?php
											foreach ( $files as $file ) {
												/*
												 * echo"<pre>";
												 * $url = moodle_url::make_pluginfile_url($file->contextid, $file->component, $file->filearea, $file->itemid, $file->filepath, $file->filename);
												 * echo"<a href='$url' download>$file->filename</a><br>";
												 * print_r(pathinfo($url));
												 * echo"</pre>";
												 */
												$filename = $file->filename;
												if ($filename !== '.' && $filename !== '..') {
													 
													$url = $CFG->wwwroot . '/pluginfile.php/' . $file->contextid . '/assignsubmission_file/submission_files/' . $file->itemid . '/' . $file->filename . '?forcedownload=1';
													echo "<a href='$url' download>$filename</a><br>";
												}
											}
											?>
                                </div>
			</div>
                            <?php endif; ?>
                    <div id="boxPostagens">
				<div class="subCabecalho fechado"
					data-block="postagem_<?=$group->id?>"><?php echo get_string('post', 'mod_tcccafeead'); ?></div>
				<div id="postagem_<?=$group->id?>" class="boxContent"
					style="display: none;">
                        <?php
										$i = 1;
										$simEstagio = false;
										$totalPostagens = 0;
										$nomeUltimaPostagem = "";
										$ultimoEnvioDeTrabalho = 0;
										$hiddenPostagem = "";
										//$buscarPostagens = $DB->get_records('tcccafeead_stage',array('tccid'=>$tcccafeead->id));
										if( count($postagens) > 0 ){
											$totalPostagens = count($postagens);
										}
										foreach ( $postagens as $postagem ) :
											$caminhoEnvios = $CFG->dataroot . '/tcccafeead/' . $tcccafeead->id . '/' . $group->id . '/postagens/' . $i;
											$caminhoCorrecoes = $CFG->dataroot . '/tcccafeead/' . $tcccafeead->id . '/' . $group->id . '/correcoes/' . $i;

											if(is_dir($caminhoEnvios)){
												if ($handle = opendir($caminhoEnvios)) {
													while (false !== ($entry = readdir($handle))) {
														if ($entry != "." && $entry != "..") {
															$aTime = filemtime($caminhoEnvios . '/' .$entry);

															if($aTime > $ultimoEnvioDeTrabalho){
																$ultimoEnvioDeTrabalho = $aTime;
																$nomeUltimaPostagem = $postagem->name;
															}

														}
													}
													closedir($handle);
												}
											}


											$simEstagio = true;



											$nArquivos = ($postagem->files > 1) ? " ".get_string('couldbepost', 'mod_tcccafeead')." $postagem->files arquivos" : "".get_string('mustbepost', 'mod_tcccafeead'). " $postagem->files ".get_string('files', 'mod_tcccafeead'). "";
											$prazo = '';
											if ($postagem->timestart > 0 || $postagem->timeend > 0) {
												$prazo .= ', disponível ';
												if ($postagem->timestart > 0) {
													$prazo .= ' de ' . date ( "d/m/Y", $postagem->timestart );
												}
												if ($postagem->timeend > 0) {
													$prazo .= ' até ' . date ( "d/m/Y", $postagem->timeend );
												}
											}
											$notaStage = $DB->get_record ( 'tcccafeead_stage_grade', array ('groupid' => $group->id,'tccid' => $tcccafeead->id,'stageid' => $postagem->id
											) );

											$notaStag = (  $notaStage == null || $notaStage->value === null ) ? '' : $notaStage->value;

											?>




                            <div class="postagem"
						data-groupid="<?=$group->id?>" data-stage="<?=$i?>"
						data-limiteenvios="<?=$postagem->files?>">



                                <?php
											$dirPostagens = C_Arquivos::mostrarPasta ( $caminhoEnvios, $tcccafeead->id, $group->id, $i, 'e', '', 'postagens', true, true );
											$dirCorrecoes = C_Arquivos::mostrarPasta ( $caminhoCorrecoes, $tcccafeead->id, $group->id, $i, 'c', '', 'correcoes', true, false );
											?>
                                <p><?=$postagem->name?><br><?=$nArquivos . $prazo?></p>
						<div class="caixaDeNotasPost">
                                <?php if($tcccafeead->stagegrade === '1'){
									$notaMaximaStage = ( $postagem->maxgrade  == 100 || $postagem->maxgrade == null ) ? 100 : $postagem->maxgrade  * 10;

									?>
                                    <label><?php echo get_string('grade', 'mod_tcccafeead'); ?> <span
								id="sucesso_<?=$tcccafeead->id?>_<?=$postagem->id?>_<?=$group->id?>"
								class="editSucesso">(<?php echo get_string('edited', 'mod_tcccafeead'); ?>)</span></label>

									<select class="apenasNumeros notaStage" data-tcc="<?=$tcccafeead->id?>"
								    data-stage="<?=$postagem->id?>" data-group="<?=$group->id?>" >
										<?php

											for($y = $notaMaximaStage; $y >= 0; $y-- ){
												$valorNotaStage =  ($postagem->maxgrade != 100 && $postagem->maxgrade <= 10 ) ? ($y / 10.0) : $y;
												if( $notaStag == $valorNotaStage ){
													$selected = "selected='selected'";
												}else{
													$selected = "";
												}
										?>

										<option value="<?=$valorNotaStage;?>" <?=$selected;?> ><?=$valorNotaStage;?></option>
										<?php

											}
										?>
									</select>



                                <?php } ?>
                                </div>
						<!-- FIM div caixaDeNotasPost-->
						<div>
							<p><?php echo get_string('sends', 'mod_tcccafeead'); ?></p>
							<span id="cArquivos_<?=$group->id . '_' . $i .'_postagens'?>">
                                        <?=$dirPostagens['html']?>
                                    </span>
						</div>
						<!-- FIM div envios-->
						<div>
							<p><?php echo get_string('adjusteds', 'mod_tcccafeead'); ?></p>
							<span id="cArquivos_<?=$group->id . '_' . $i .'_correcoes'?>">
                                        <?=$dirCorrecoes['html']?>

                                    </span>

						</div>
						<!-- FIM div correcoes-->
						<div>
							<input type="file" class="upload"
								id="iF_<?=$group->id . '_' . $i?>"
								data-groupid="<?=$group->id?>" data-stage="<?=$i?>" data-maxfile="<?=$postagem->files?>" data-role="coord" multiple /><label
								for="iF_<?=$group->id . '_' . $i?>"
								style="float: right; margin: 5px;"></label>
						</div>
						<!-- FIM div fileupload-->

					</div>

					<!-- FIM div postagem-->
                        <?php
											$i ++;
										endforeach
										;
										?>
                        </div>
				<!-- FIM boxPostagem Grupo-->
			<input type="hidden" id="postagem_id_<?=$group->id?>" class="status_<?=$group->id?>" value="<?=$nomeUltimaPostagem?>" />
			</div>
			<!-- FIM boxPostagens-->
			<div id="boxNotas">

				<div class="subCabecalho fechado" data-block="notas_<?=$group->id?>"><?php echo get_string('grades', 'mod_tcccafeead'); ?></div>
				<!-- FIM subCabecalho-->
				<div id="notas_<?=$group->id?>" style="display: none;">
                            <?php
										if ($tcccafeead->stagegrade === '0') {
											$notaTC = $DB->get_record ( 'tcccafeead_grade', array ('groupid' => $group->groupid,'tccid' => $tcccafeead->id,'type' => 1
											) );

											$notaT = (($notaTC == false || $notaTC == null) || $notaTC->value === null) ? '' : $notaTC->value;
											$disableNotaT = ($tccgroup->status == '7') ? ' disabled' : '';
										} else {
											$notasStage = $DB->get_records ( 'tcccafeead_stage_grade', array ('groupid' => $group->groupid,'tccid' => $tcccafeead->id
											) );
											if (count ( $notasStage ) > 0) {
												$somaNotasT = 0;
												foreach ( $notasStage as $ns ) {
													$somaNotasT += $ns->value;
												}
												$media = round ( $somaNotasT , 2 );
												$notaT = ($somaNotasT > 0) ? $media : '';
											} else {
												$notaT = '';
											}
											$disableNotaT = ($tccgroup->status == '7') ? ' disabled' : '';
										}
										$disableNotaA = ($tccgroup->status == '7') ? ' disabled' : '';

										$notaAC = $DB->get_record ( 'tcccafeead_grade', array ('groupid' => $group->groupid,'tccid' => $tcccafeead->id,'type' => 2
										) );

										if ($notaAC !== FALSE) {
											$notaA = ($notaAC->value === null) ? '' : $notaAC->value;

											$temaTcc = $notaAC->tema;

											if ($notaAC->data == 0) {
												$dataTcc = "";
											} else {
												$date = new DateTime ( "@" . $notaAC->data );
												$dataTcc = $date->format ( 'Y-m-d' );
											}
										} else {
											$notaA = '';
											$temaTcc = '';
										}

										if ($tcccafeead->levelid == "2") {
											$media = ($notaT !== '' && $notaA !== '') ? round ( ($notaT + $notaA) / 2, 2 ) : '';
										}
										if ($tcccafeead->levelid == "1") {
											$media = ($notaT !== '' && $notaA !== '') ? round ( ($notaT + $notaA), 2 ) : '';
										}
										?>
                            <div class="caixaDeNotas cols3">
								<label><?php echo get_string('grade_work', 'mod_tcccafeead'); ?> <?=($tcccafeead->stagegrade === '1')?' ('. get_string('sum_post', 'mod_tcccafeead'). ')': ''?><span
									id="sucessoNT_<?=$group->id?>" class="editSucesso">(editado)</span></label>

							<?php
								if( $tcccafeead->stagegrade === '1' ){
									?>
									<input type="text" id="nt_<?=$group->groupid?>" class="notaGrupo apenasNumeros txtTrabalho" value="<?=$notaT?>"
										data-levelid="<?=$tcccafeead->levelid?>" data-type="1"
										data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->groupid?>"
										disabled />
									<?php
								}else{

									?>

									<select id="nt_<?=$group->groupid?>" data-levelid="<?=$tcccafeead->levelid?>" data-type="1"
										data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->groupid?>"
										<?=$disableNotaT?> class="notaGrupo txtTrabalho" data-notaTC="<?=$notaT?>" data-banca="<?=$tcccafeead->banca?>" data-postagem="<?=$totalPostagens?>" >
										<?php
											$notaMaxima = ( $tcccafeead->grade == 100 ) ? 100 : $tcccafeead->grade * 10;
											for($w = $notaMaxima; $w >= 0; $w-- ){
												$valorNota =  ( $tcccafeead->grade != 100 && $tcccafeead->grade <= 10 ) ? ($w / 10.0) : $w;
												if( $notaT == $valorNota ){
													$selected = "selected='selected'";
												}else{
													$selected = "";
												}
										?>

										<option value="<?=$valorNota;?>" <?=$selected;?> ><?=$valorNota;?></option>
										<?php

											}
										?>
									</select>

									<?php
								}
							?>


						</div>
					<!-- FIM caixaDeNotas cols3-->

                            <?php if($tcccafeead->banca === '1'): ?>

                            	<div class="caixaDeNotas cols3">
									<label><?php echo get_string('grade_presentation', 'mod_tcccafeead'); ?> <span id="sucessoNA_<?=$group->groupid?>" class="editSucesso">(<?php echo get_string('edited', 'mod_tcccafeead'); ?>)</span></label></label>


									<select id="na_<?=$group->groupid?>" data-levelid="<?=$tcccafeead->levelid?>" data-type="2"
										data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->groupid?>"
										<?=$disableNotaA?> class="notaGrupo txtTrabalho" >
										<?php
											$notaMaximaBanca = ( $tcccafeead->grade_banca == 100 ) ? 100 : $tcccafeead->grade_banca * 10;
											for($z = $notaMaximaBanca; $z >= 0; $z-- ){
												$valorNotaBanca =  ( $tcccafeead->grade_banca != 100 && $tcccafeead->grade_banca <= 10 ) ? ($z / 10.0) : $z;
												if( $notaA == $valorNotaBanca ){
													$selected = "selected='selected'";
												}else{
													$selected = "";
												}
												?>

												<option value="<?=$valorNotaBanca;?>" <?=$selected;?> ><?=$valorNotaBanca;?></option>
												<?php

											}
										?>
									</select>
								</div>
					<!-- FIM caixaDeNotas cols3-->

                                 <?php if ($tccgroup->status == 6 || $tccgroup->status == 7 ){?>
                                	 <div class="caixaDeNotas cols3">
										<label><?php echo get_string('date', 'mod_tcccafeead'); ?> <span id="dataTcc_<?=$group->groupid?>"
										class="editSucesso">(<?php echo get_string('edited', 'mod_tcccafeead'); ?>)</span></label></label> <input
										type="date" id="data_<?=$group->groupid?>"
										class="dataTcc extraGrupo camposExtras" placeholder=""
										value="<?=$dataTcc?>" data-valor="<?=$notaA?>"
										data-levelid="<?=$tcccafeead->levelid?>" data-type="3"
										data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->groupid?>"
										<?=$disableNotaA?> />
									</div>
									<!-- FIM caixaDeNotas cols3-->
								<?php }?>
                            <?php endif; ?>
                            <?php if($mostrarTema == true): ?>
								<div class="caixaDeNotas cols1">
									<label><?php echo get_string('theme', 'mod_tcccafeead'); ?> <span id="temaTcc_<?=$group->groupid?>" class="editSucesso">(<?php echo get_string('edited', 'mod_tcccafeead'); ?>)</span></label></label>
									<input type="text" id="tema_<?=$group->groupid?>"
										class="temaTcc extraGrupo camposExtras" value="<?=$temaTcc?>"
										data-valor="<?=$notaA?>" data-levelid="<?=$tcccafeead->levelid?>"
										data-type="3" data-tcc="<?=$tcccafeead->id?>"
										data-group="<?=$group->groupid?>" <?=$disableNotaA?> />

								</div>
								<!-- FIM caixaDeNotas cols1-->
							<?php endif; ?>
							<?php if($tcccafeead->banca === '1'): ?>
								<div class="caixaDeNotas">
								<label><?php echo get_string('final_grade', 'mod_tcccafeead'); ?></label> <input type="text"
								id="nm_<?=$group->groupid?>" class="apenasNumeros"
								value="<?=$media?>" disabled />
								</div>
								<!-- FIM caixaDeNotas -->

                            <?php endif; ?>
                            <div class="notasBots">
							<?php
								$mostrarFinalizar = true;
								$mostrarAta = false;

								if( $tcccafeead->banca == 0 && $totalPostagens == 0){
									$mostrarFinalizar = false;
								}
							?>

						<?php if( $mostrarFinalizar  ) { ?>
							<button class="fecharNotas" data-tcc="<?=$tcccafeead->id?>"
							data-group="<?=$group->groupid?>"><?php echo get_string('finish', 'mod_tcccafeead'); ?></button>
						<?php }?>


                                <?php if ($tccgroup->status == 7 ){?>


                                <?php }?>
                                <button class="abrirNotas"
							data-tcc="<?=$tcccafeead->id?>" data-group="<?=$group->groupid?>"><?php echo get_string('reopen', 'mod_tcccafeead'); ?></button>
					</div>
					<!-- FIM NOTASBOTS -->
				</div>
				<!-- FIM NOTA_GRUPO -->
			</div>
			<!-- FIM BOXNOTA -->
		</div>
		<!-- FIM GROUPDETALHE -->

	</div>
	<!-- FIM GROUPCONTAINER -->


	<?php
									} catch ( Exception $ex ) {
										echo $ex->getMessage ();
									}
								endforeach
								;
								?>
								<table class="paginacao">
		<?php
			for($i=0; $i < $totalPaginas; $i++){
				?>
				<td class="<?=($pageNumber == $i)?'current':'pagina'?>" > <a href="./view.php?id=<?=$courseModule->id?>&page=<?=$i?>" ><?=($i+1)?></a> </td>
				<?php

			}
		?>
	</table>
</div>
<div id="printForm"> </div>

<iframe id="downloadFrame" name="downloadFrame" style="display: none;"></iframe>
<script>

								$(function(){



									$('.btnPrint').click(function(e) {

										e.preventDefault();

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


										return true;
									});

									$(".s_3").each(function(index){
										console.log("GRUOP:" + $("#postagem_id_"+$(this).data("groupid")).val() );
										$(this).text( $(this).text() + ": " + $("#postagem_id_"+$(this).data("groupid")).val() );
									});

								});


							</script>
