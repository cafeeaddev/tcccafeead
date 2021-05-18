<?php
    /**
    * Classe model
    *
    * @author CafeEAD
    */
    class M_TCC
    {
        public static function inserir($form){
            global $DB;
            global $CFG;
            require_once($CFG->dirroot. '/lib/modinfolib.php');
            $course = $DB->get_record('course', array('id'=>$form['course']));
            $module = $DB->get_record('modules', array('name'=>'tcccafeead'));
            $section = $DB->get_record('course_sections', array('course'=>$form['course'], 'section'=>$form['section']));

            //criando nova instancia de tcccafeead
            $tcccafeead = new stdClass();
            $tcccafeead->course = $form['course'];
            $tcccafeead->name = $form['name'];
            $tcccafeead->intro = $form['intro'];
            $tcccafeead->banca = ($form['banca'] === '1')?1:0;
            $tcccafeead->stagegrade = ($form['stagegrade'] === '1')?1:0;
            $tcccafeead->introformat = 1;
            $tcccafeead->importfrom = $form['importfrom'];
            $tcccafeead->importfromassign = $form['importfromassign'];
            $tcccafeead->timecreated = time();
            $tcccafeead->timemodified = time();
            $tcccafeead->timestart = ($form['availablefrom'] !== '')?strtotime($form['availablefrom']): NULL;
            $tcccafeead->timeend = ($form['availableto'] !== '')?strtotime($form['availableto']) + 75599: NULL;
            $tcccafeead->grade = ( isset($form['maxgrade']) && $form['maxgrade'] != '')? $form['maxgrade'] : NULL;
			      $tcccafeead->grade_banca = ( isset($form['maxbanca']) && $form['maxbanca'] != '')? $form['maxbanca'] : NULL;
            $tcccafeead->levelid = $form['level'];

            //inserindo no banco de dados
            $insertTcc = $DB->insert_record_raw('tcccafeead', $tcccafeead);

            //criando gradeItem
            self::criaGradeItem($form['course'], $insertTcc, $tcccafeead->name, $DB);

            //criando nova instancia de course_modules (associação entre o módulo e o curso)
            $courseModule = new stdClass();
            $courseModule->course = $form['course'];
            $courseModule->module = $module->id;
            $courseModule->instance = $insertTcc;
            $courseModule->section  = $section->id;
            $courseModule->added = time();
            $course_module->showdescription = (isset(  $form['showIntro'] ) && $form['showIntro'] === '1')?1:0;
            $courseModule->availablefrom = ($form['availablefrom'] !== '')?strtotime($form['availablefrom']): 0;
            $courseModule->availableuntil = ($form['availableto'] !== '')?strtotime($form['availableto']): 0;
            $courseModule->showavailability = ($form['showAvailability'] == 1)? 1 : 0;


            $insertCM = $DB->insert_record('course_modules', $courseModule);

            //busco todos tipos de formulário obrigatório que um tcc pode ter
            $formTypes = $DB->get_records_select('tcccafeead_form_type', '', array(), 'id', '*', 0, 999999);
            //percorro eles
            foreach($formTypes as $formType){
                if(isset($form['form_' . $formType->id])){
                    $iFormType = new stdClass();
                    $iFormType->tccid = $insertTcc;
                    $iFormType->formtypeid = $formType->id;

                    //inserindo no banco de dados
                    $insertFT = $DB->insert_record('tcccafeead_form', $iFormType);
                }
            }

            $i = 1;
            while(isset($form['Ipostagem' . $i])){

                    $iStage = new stdClass();
                    $iStage->tccid = $insertTcc;
                    $iStage->name = $form['Ipostagem' . $i];
                    if(isset($form['pInicio' .$i])){
                        $iStage->timestart = ($form['pInicio' . $i] !== '')? strtotime($form['pInicio' . $i]) : 0;
                    }
                    if(isset($form['pFim' .$i])){
                        $iStage->timeend = ($form['pFim' . $i] !== '')? strtotime($form['pFim' . $i]) : 0;
                    }
                    $iStage->files = $form['pNumeroArquivos' . $i];
					$iStage->maxgrade = ( isset($form['pMaxGrade' . $i]) && $form['pMaxGrade' . $i] != '')? $form['pMaxGrade'. $i] : NULL;

                    //inserindo no banco de dados
                    $insertStage = $DB->insert_record('tcccafeead_stage', $iStage);


                $i++;
            }

            //Atualizando curso
//            $course->sectioncache = '';
//            $DB->update_record('course', $course);

            //Atualizando section

            $section->sequence = $section->sequence . ',' . $insertCM;

            $DB->update_record('course_sections', $section);
            $rebuild = rebuild_course_cache($course->id);
            return $insertTcc;

        }

        public static function atualizar($form){
            global $DB;
            global $CFG;
            require_once($CFG->dirroot. '/lib/modinfolib.php');
			      $course = $DB->get_record('course', array('id'=>$form['course']));
            $module = $DB->get_record('modules', array('name'=>'tcccafeead'));
            $section = $DB->get_record('course_sections', array('course'=>$form['course'], 'section'=>$form['section']));


            //instanciando course_module
			      $course_module = $DB->get_record('course_modules', array('course'=>$course->id, 'section'=>$section->id, 'module'=>$module->id));


            //atualizando course_module
            $course_module->showdescription = (isset(  $form['showIntro'] ) && $form['showIntro'] === '1')?1:0;
            $course_module->showavailability = (isset($form['showAvailability']))? $form['showAvailability'] : 0;

            $DB->update_record('course_modules', $course_module);

            //instanciando tcc
            $tcccafeead = $DB->get_record('tcccafeead', array('id'=>$course_module->instance));


            //Atualizando tcc
            $tcccafeead->name = $form['name'];
            $tcccafeead->intro = $form['intro'];
            $tcccafeead->banca = ($form['banca'] === '1')?1:0;
            $tcccafeead->stagegrade = ($form['stagegrade'] === '1')?1:0;
            $tcccafeead->importfrom = $form['importfrom'];
            $tcccafeead->importfromassign = $form['importfromassign'];
            $tcccafeead->levelid = $form['level'];
            $tcccafeead->timemodified = time();
            $tcccafeead->timestart = ($form['availablefrom'] !== '')?strtotime($form['availablefrom']): NULL;
            $tcccafeead->timeend = ($form['availableto'] !== '')?strtotime($form['availableto']) + 75599: NULL;
			      $tcccafeead->grade = ( isset($form['maxgrade']) && $form['maxgrade'] != '')? $form['maxgrade'] : NULL;
			      $tcccafeead->grade_banca = ( isset($form['maxbanca']) && $form['maxbanca'] != '')? $form['maxbanca'] : NULL;

            $DB->update_record_raw('tcccafeead', $tcccafeead);

            //verificando se existe gradeItem
            $gradeItem = $DB->get_record('grade_items', array('courseid'=>$course_module->course, 'itemtype'=>'mod', 'itemmodule'=>'tcccafeead', 'iteminstance'=>$tcccafeead->id));
            //caso não tenha, cria
            if($gradeItem === false){
                self::criaGradeItem($course_module->course, $tcccafeead->id, $tcccafeead->name, $DB);
            }

            //busco todos tipos de formulário obrigatório que um tcc pode ter
            $formTypes = $DB->get_records_select('tcccafeead_form_type', '', array(), 'id', '*', 0, 999999);
            //percorro eles
            foreach($formTypes as $formType){
                //vefifico se o tcc já tinha associação com este tipo de formulário
                $oFormType = $DB->get_record('tcccafeead_form', array('tccid'=>$course_module->instance, 'formtypeid'=>$formType->id));
                //já tinha
                if($oFormType !== FALSE){
                    //não deve mais ter
                    if(!isset($form['form_' . $formType->id])){
                        $delete = $DB->delete_records('tcccafeead_form', array('id'=>$oFormType->id));
                    }
                }
                //não tinha
                else{
                    //agora deve ter
                    if(isset($form['form_' . $formType->id])){
                        $iFormType = new stdClass();
                        $iFormType->tccid = $tcccafeead->id;
                        $iFormType->formtypeid = $formType->id;

                        //inserindo no banco de dados
                        $insert = $DB->insert_record('tcccafeead_form', $iFormType);
                    }
                }
            }

            //carregando postagens
            $postagens = $DB->get_records_list('tcccafeead_stage', 'tccid', array($tcccafeead->id), 'id', '*', 0, 999999);
            $postagens = array_values($postagens);

            //percorrendo e atualizando postagens
            $i = 1;
            while(isset($form['Ipostagem' . $i])){
                //existe e deve ser atualizada
                if(isset($postagens[$i-1])){
                    $aStage = $postagens[$i-1];
                    $aStage->name = $form['Ipostagem' . $i];
                    if(isset($form['pInicio' .$i])){
                        $aStage->timestart = ($form['pInicio' . $i] !== '')? strtotime($form['pInicio' . $i]) : 0;
                    }
                    if(isset($form['pFim' .$i])){
                        $aStage->timeend = ($form['pFim' . $i] !== '')? strtotime($form['pFim' . $i]) : 0;
                    }
                    $aStage->files = $form['pNumeroArquivos' . $i];

					$aStage->maxgrade = ( isset($form['pMaxGrade' . $i]) && $form['pMaxGrade' . $i] != '')? $form['pMaxGrade'. $i] : NULL ;



					$DB->update_record('tcccafeead_stage', $aStage);
                }
                //nova deve ser criada
                else{
                    $iStage = new stdClass();
                    $iStage->tccid = $tcccafeead->id;
                    $iStage->name = $form['Ipostagem' . $i];
                    if(isset($form['pInicio' .$i])){
                        $iStage->timestart = ($form['pInicio' . $i] !== '')? strtotime($form['pInicio' . $i]) : 0;
                    }
                    if(isset($form['pFim' .$i])){
                        $iStage->timeend = ($form['pFim' . $i] !== '')? strtotime($form['pFim' . $i]) : 0;
                    }
                    $iStage->files = $form['pNumeroArquivos' . $i];
					$iStage->maxgrade = ( isset($form['pMaxGrade' . $i]) && $form['pMaxGrade' . $i] != '')? $form['pMaxGrade'. $i] : NULL;;
                    //inserindo no banco de dados
                    $insert = $DB->insert_record('tcccafeead_stage', $iStage);

                }
                $i++;
            }
            //deve ser excluída
            While(isset($postagens[$i-1])){
                $dStage = $postagens[$i-1];
                $delete = $DB->delete_records('tcccafeead_stage', array('id'=>$dStage->id));
                $i++;
            }

            //instanciando course
            $course = $DB->get_record('course', array('id'=>$course_module->course));

            //Atualizando course
//            $course->sectioncache = '';
//            $DB->update_record('course', $course);
            rebuild_course_cache($course->id);

            return 1;


        }
        public static function criaGradeItem($course, $tcc, $name, $DB){

            $gradeCat = $DB->get_record('grade_categories', array('courseid'=>$course, 'depth'=>1));
            if($gradeCat === false){
                $gc = new stdClass();
                $gc->courseid = $course;
                $gc->depth = 1;
                $gc->fullname = '?';
                $gc->timecreated = time();
                $gc->timemodified = time();
                $gcId = $insertGradeItem = $DB->insert_record_raw('grade_categories', $gc);
                $gradeCat = $DB->get_record('grade_categories', array('id'=>$gcId, 'depth'=>1));
            }
            if(is_object($gradeCat)){
                $gi = new stdClass();
                $gi->courseid = $course;
                $gi->itemname = $name;
                $gi->categoryid = $gradeCat->id;
                $gi->itemtype = 'mod';
                $gi->itemmodule = 'tcccafeead';
                $gi->iteminstance = $tcc;
                $gi->itemnumber = 0;
                $gi->gradetype = 1;
                $gi->grademax = 100;
                $gi->grademin = 0;
                $gi->timecreated = time();
                $gi->timemodified = time();
                $insertGradeItem = $DB->insert_record_raw('grade_items', $gi);
                return $insertGradeItem;
            }
        }
    }
