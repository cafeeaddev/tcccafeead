<?php
    /**
    * Classe view
    *
    * @author CafeEAD
    */
    class V_FormularioConfiguracao
    {

        public static function montar ($update, $courseid)
        {
            global $DB;
            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $urlAtual = $protocolo . '' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $urlAtualArray = explode('/mod', $urlAtual);
            $urlModulo = $urlAtualArray[0].'/mod/tcccafeead';

            if($update !== '' && $update != '0'){

                $course_module = $DB->get_record('course_modules', array('id'=>$update));
                $tcccafeead = $DB->get_record('tcccafeead', array('id'=>$course_module->instance));

                ?>

                <?php

                $courseid = $course_module->course;
            }else{
                $course_module = $DB->get_record('course_modules', array('id'=>$update));

            }

        		if( $course_module == false){
        			$course_module = new stdClass();
        			$course_module->showdescription = 0;
        		}
            $tcccafeeadId = (isset($tcccafeead->id))? $tcccafeead->id : '';
            $course = $DB->get_record('course', array('id'=>$courseid));

            //$category = $DB->get_record('course_categories', array('id'=>$course->category));
            $categoryCourses = $DB->get_records('course', array(), 'fullname', 'id, fullname, shortname', 0, 999999);
            $levels = $DB->get_records('tcccafeead_level', array(), 'name', '*', 0, 999999);
            $formTypes = $DB->get_records_select('tcccafeead_form_type', '', array(), 'id', '*', 0, 999999);
            $postagens = $DB->get_records_list('tcccafeead_stage', 'tccid', array($tcccafeeadId), 'id', '*', 0, 999999);

        ?>
        <link rel="stylesheet" type="text/css" href="<?=$urlModulo?>/css/estilo.css" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="<?=$urlModulo?>/css/form.css" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="<?=$urlModulo?>/materialG/css/materialG.css" charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="<?=$urlModulo?>/materialG/css/inputDate.css" charset="utf-8"/>
        <script type="text/javascript" src="<?=$urlModulo?>/materialG/js/materialG.js"></script>
        <script type="text/javascript" src="<?=$urlModulo?>/materialG/js/inputDate.js"></script>


            <div class="grid form" id="formTCC">

                <div class="l100 gh1">
                    <input type="text" id="fName" name="name" data-row = "name" class="obrigatorio" placeholder="" value="<?=(isset($tcccafeead))?$tcccafeead->name:''?>"/><label for="fName" title="Campo Obrigatório"><?php echo get_string('name', 'mod_tcccafeead'); ?> <span>*</span></label>
                </div>
                <div class="l100 gh2">
                    <textarea id="fIntro" name="intro" data-row = "intro"><?=(isset($tcccafeead))?$tcccafeead->intro:''?></textarea><label for="descricao"><?php echo get_string('description', 'mod_tcccafeead'); ?></label>

                </div>
                <div class="l100" style="margin-top: 0; margin-bottom: 0; text-align: right;">
                    <?php $checked = ($update === '' || $course_module->showdescription == 1)?'checked' : '';?>
                    <input type="checkbox" id="showIntro" name="showIntro" value="1" <?=$checked?> /><label for="showIntro"><?php echo get_string('show_description', 'mod_tcccafeead'); ?></label>
                </div>
                <div class="l50 gh1" >
                    <?php
                        if(isset($tcccafeead)){
                            $dataAvailableFrom = ($tcccafeead->timestart !== 0 && $tcccafeead->timestart !== null)? date("Y-m-d", $tcccafeead->timestart) : '';
                            $dataAvailableUntil = ($tcccafeead->timeend !== 0 && $tcccafeead->timeend !== null)? date("Y-m-d", $tcccafeead->timeend) : '';
                        }else{
                            $dataAvailableFrom = '';
                            $dataAvailableUntil = '';
                        }
                    ?>
                    <input type="date" id="favailablefrom" name="availablefrom" placeholder="" value="<?=$dataAvailableFrom?>"/><label for="favailablefrom"><?php echo get_string('availablefrom', 'mod_tcccafeead'); ?></label>
                </div><div class="l50 gh1" >
                    <input type="date" id="favailableto" name="availableto" placeholder="" value="<?=$dataAvailableUntil?>"/><label for="favailableto"><?php echo get_string('availableuntil', 'mod_tcccafeead'); ?></label>
                </div>
                <?php
                /*<div class="l100" style="margin-top: 0; margin-bottom: 0; text-align: right;">
                    <?php $checked = ($update === '' || $course_module->availability == 1)?'checked' : '';?>
                    <input type="checkbox" id="showAvailability" name="showAvailability" value="1" <?=$checked?> /><label for="showAvailability">Mostrar Disponibilidade</label>
                </div>*/
                ?>
                <div class="120 gh1" id="cNivelDeEnsino">


                    <select id="level" name="level" class="obrigatorio">
                        <option style="display: none;" value="" class='none'><?php echo get_string('choice', 'mod_tcccafeead'); ?></option>
                        <?php
                        foreach($levels as $level){
                          $barraEstagio = ($level->id === '1')? ' / Estágio': '';
                            $selected = (isset($tcccafeead) && $level->id === $tcccafeead->levelid)? 'selected' : '';
                            echo"<option value='$level->id' $selected >$level->name $barraEstagio</option>";
                        }
                        ?>
                    </select><label for="level" title="Campo Obrigatório"><?php echo get_string('level', 'mod_tcccafeead'); ?> <span>*</span></label>
                </div><div class="120 gh1" id="cBanca">
                    <h2><?php echo get_string('examing', 'mod_tcccafeead'); ?></h2>
                    <?php

                        $checked = (isset($tcccafeead) && $tcccafeead->banca == 0)? 'checked' : '';
                        $checked2 = (isset($tcccafeead) && $tcccafeead->banca == 0)? '' : 'checked';

                    ?>
                    <input type="checkbox" id = "banca" value = "0" name='banca' <?=$checked?>/><label for="banca">Não</label>
                    <input type="checkbox" id = "banca2" value = "1" name='banca' <?=$checked2?>/><label for="banca2">Sim</label>
                </div>
				<div class="120 gh1" id="cBancaPostagem">
                    <h2><?php echo get_string('post_grade', 'mod_tcccafeead'); ?></h2>
                    <?php

                        $checked = (isset($tcccafeead) && $tcccafeead->stagegrade === '1')? '' : 'checked';
                        $checked2 = (isset($tcccafeead) && $tcccafeead->stagegrade === '1')? 'checked' : '';

                    ?>
                    <input type="checkbox" id = "stagegrade" value = "0" name='stagegrade' <?=$checked?>/><label for="stagegrade"><?php echo get_string('no', 'mod_tcccafeead'); ?></label>
                    <input type="checkbox" id = "stagegrade2" value = "1" name='stagegrade' <?=$checked2?>/><label for="stagegrade2"><?php echo get_string('yes', 'mod_tcccafeead'); ?></label>
                </div>

				<div class="120 gh1" id="cBancaMaxima">
                    <h2><?php echo get_string('maxgrade', 'mod_tcccafeead'); ?></h2>
                    <input type = "text" class=" apenasNumeros" id="maxgrade" name="maxgrade" value="<?=(isset($tcccafeead))?$tcccafeead->grade:''?>" maxlength="3"/>
                </div>

				<div class="120 gh1" id="cBancaMaximaBanca">
                    <h2><?php echo get_string('maxgrade_examing', 'mod_tcccafeead'); ?></h2>
                    <input type = "text" class=" apenasNumeros" id="maxbanca" name="maxbanca" value="<?=(isset($tcccafeead))?$tcccafeead->banca:''?>" maxlength="3"/>
                </div>

                <div class="l100 gh1" id="cFormulario">
                    <h2><?php echo get_string('mandatory_form', 'mod_tcccafeead'); ?></h2>
                    <?php
                    foreach($formTypes as $ft){
						$checkedForm = '';
                        if(isset($tcccafeead)){
                            $ftTemp = $DB->get_record('tcccafeead_form', array('tccid'=>$tcccafeead->id, 'formtypeid'=>$ft->id));
                            $checkedForm = ($ftTemp !== FALSE)? 'checked': '';
                        }
                        echo "<input type='checkbox' id='form_$ft->id' name='form_$ft->id' class='formTypes' value='$ft->id' $checkedForm/><label for='form_$ft->id'>$ft->name</label>";
                    }
                    ?>
                </div><br><br>
                <div class="l100 gh1" id="cursoImp">
                    <select id="importfrom" name="importfrom">
                        <option value = 0><?php echo get_string('notcc_activity', 'mod_tcccafeead'); ?></option>
                        <?php
                        foreach($categoryCourses as $catCourse){

                            $cTccs = $DB->get_records('tcccafeead', array('course'=>$catCourse->id), 'id', '*', 0, 999999);
                            foreach($cTccs as $cTcc){
                                $selected = ($cTcc->id === $tcccafeead->importfrom)?'selected':'';
                                if($tcccafeead->id !== $cTcc->id){
                                    echo '<option value = "'. $cTcc->id . '" ' . $selected . '> '. $catCourse->fullname . ' - '. $cTcc->name .'</option>';
                                }
                            }
                        }
                        ?>

                    </select><label for="cursoImportar" id="l_cursoImportar"><?php echo get_string('import_tcc', 'mod_tcccafeead'); ?></label>
                </div>
                <div class="l100 gh1" id="cursoImp">
                    <select id="importfromassign" name="importfromassign">
                        <option value = 0><?php echo get_string('noassign_activity', 'mod_tcccafeead'); ?></option>
                        <?php
                        foreach($categoryCourses as $catCourse){

                            $cTccs = $DB->get_records('assign', array('course'=>$catCourse->id), 'id', '*', 0, 999999);
                            foreach($cTccs as $cTcc){
                                $selected = ($cTcc->id === $tcccafeead->importfromassign)?'selected':'';
                                if($tcccafeead->id !== $cTcc->id){
                                    echo '<option value = "'. $cTcc->id . '" ' . $selected . '> '. $catCourse->fullname . ' - '. $cTcc->name .'</option>';
                                }
                            }
                        }
                        ?>

                    </select><label for="cursoImportar" id="l_cursoImportar"><?php echo get_string('import_assign', 'mod_tcccafeead'); ?></label>
                </div>
                <div class="l100">
                    <h2><?php echo get_string('post', 'mod_tcccafeead'); ?></h2>

                    <div id="postagens">
                        <?php
                        $i = 1;
                        if($update !== ''){
                            foreach($postagens as $postagem){
                                $pTimeInicio = ($postagem->timestart > 0)? date("Y-m-d", $postagem->timestart) : '';
                                $pTimeFim = ($postagem->timeend > 0)? date("Y-m-d", $postagem->timeend) :  '';
                                ?>
                               <div class="postagem" id="postagem<?=$i?>" data-item="<?=$i?>">
                                   <span>
                                       <input type="text" id="Ipostagem<?=$i?>" name="Ipostagem<?=$i?>" value="<?=$postagem->name?>">
                                   </span><span>
                                       <span class="ico16 icoExcluir"></span>
                                       <span class="ico16 icoConfig"></span>
                                   </span><div class="internodate">
                                       <span><label for="pInicio<?=$i?>"><?php echo get_string('start', 'mod_tcccafeead'); ?>:</label> <input type="date" id="pInicio<?=$i?>" class="pIni" name="pInicio<?=$i?>" data-i="<?=$i?>" value="<?=$pTimeInicio?>"></span>
                                       <span><label for="pInicio<?=$i?>"><?php echo get_string('end', 'mod_tcccafeead'); ?>:</label> <input type="date" id="pFim<?=$i?>" name="pFim<?=$i?>" data-i="<?=$i?>" value="<?=$pTimeFim?>"></span>
                                       <span><?php echo get_string('file_post', 'mod_tcccafeead'); ?> <input type = "text" class="nFiles apenasNumeros" id="pNumeroArquivos<?=$i?>" name="pNumeroArquivos<?=$i?>" value="<?=$postagem->files?>" maxlength="2"/></span>
									   <span><?php echo get_string('maxgrade', 'mod_tcccafeead'); ?> <input type = "text" class="nMaxGrade apenasNumeros" id="pMaxGrade<?=$i?>" name="pMaxGrade<?=$i?>" value="<?=$postagem->maxgrade?>" maxlength="3"/></span>
                                   </div>
                               </div>
                            <?php
                            $i++;
                            }
                        }else{
                            while($i < 5){?>
                               <div class="postagem" id="postagem<?=$i?>" data-item="<?=$i?>">
                                    <span>
                                        <input type="text" id="Ipostagem<?=$i?>" name="Ipostagem<?=$i?>" value="Postagem <?=$i?>">
                                    </span><span>
                                        <span class="ico16 icoExcluir"></span>
                                        <span class="ico16 icoConfig"></span>
                                    </span><div class="internodate">
                                        <span><label for="pInicio<?=$i?>">Inicio:</label> <input type="date" id="pInicio<?=$i?>" name="pInicio<?=$i?>"></span>
                                       <span> <label for="pInicio<?=$i?>">Término:</label> <input type="date" id="pFim<?=$i?>" name="pFim<?=$i?>"></span>
                                        <span><?php echo get_string('file_post', 'mod_tcccafeead'); ?> <input type = "text" class="nFiles apenasNumeros" id="pNumeroArquivos<?=$i?>" name="pNumeroArquivos<?=$i?>" value="1" maxlength="2"/></span>
									<span<?php echo get_string('maxgrade', 'mod_tcccafeead'); ?> <input type = "text" class="nMaxGrade apenasNumeros" id="pMaxGrade<?=$i?>" name="pMaxGrade<?=$i?>" maxlength="3"/></span>
									</div>
                               </div>
                            <?php
                            $i++;
                            }
                        }
                        ?>
                    </div>

                    <span id="novaPostagem" class="ico32 icoIncluir" style="float: right;margin: 10px;" title="incluir postagem"></span>
                </div>
                <div class="l100 gh1">
<!--                    <button id="fEnviar" data-acao="<?=($update === '')?'inserir':'atualizar';?>">
                        salvar
                    </button>-->
                    <div style="text-align: right;">
                        <button id="fConfirmar" data-teste="<?=$update;?>"  data-acao="<?=( $update == null || $update == false || $update==0 || $update == '')?'inserir':'atualizar';?>">
                            <?php echo get_string('confirm', 'mod_tcccafeead'); ?>
                        </button>
                    </div>
<!--                    <button id="fEnviar" data-acao="<?=($update === '')?'inserir':'atualizar';?>">
                        salvar e mostrar
                        <?=($update === '')?'inserir':'atualizar';?>
                    </button>
                    <button>cancelar</button>-->
                </div>
            </div>


            <?php
        }


    }
?>
