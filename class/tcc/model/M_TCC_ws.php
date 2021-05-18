<?php 
    /**
    * Classe model
    *
    * @author CafeEAD
    */
    //include_once('../../../../../config.php');
    require_once($CFG->libdir . "/externallib.php");
    require "M_TCC.php";
    class M_TCC_ws extends external_api 
    {
	public static function inserir_parameters(){
	    return new external_function_parameters(
                array(
                'course' => new external_value(PARAM_INT, 'CURSO', VALUE_DEFAULT, ''),
                'section' => new external_value(PARAM_INT, 'section', VALUE_DEFAULT, ''),
                'name' => new external_value(PARAM_TEXT, 'nome', VALUE_DEFAULT, ''),
                'intro' => new external_value(PARAM_TEXT, 'intro', VALUE_DEFAULT, ''),
                'banca' => new external_value(PARAM_INT, 'banca', VALUE_DEFAULT, '0'),
                'stagegrade' => new external_value(PARAM_INT, 'nota por postagem', VALUE_DEFAULT, '0'),
                'importfrom' => new external_value(PARAM_INT, 'importar de outra atividade de tcc', VALUE_DEFAULT, '0'),
                'importfromassign' => new external_value(PARAM_INT, 'importar arquivos de assign', VALUE_DEFAULT, '0'),
                'availablefrom' => new external_value(PARAM_TEXT, 'disponível a partir', VALUE_DEFAULT, ''),
                'availableto' => new external_value(PARAM_TEXT, 'disponível a partir', VALUE_DEFAULT, ''),
                'level' => new external_value(PARAM_INT, 'nível', VALUE_DEFAULT, '1'),
                'showintro' => new external_value(PARAM_INT, 'Mostrar Descrição', VALUE_DEFAULT, '0'),
                'showavailability' => new external_value(PARAM_INT, 'mostrar disponibilidade', VALUE_DEFAULT, '0'),
                'formtypes' => new external_value(PARAM_TEXT, 'formulários obrigatórios', VALUE_DEFAULT, ''),
                'postagens' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_TEXT, 'nome da postagem', VALUE_DEFAULT, ''),
                            'begin' => new external_value(PARAM_TEXT, 'nome da postagem', VALUE_DEFAULT, ''),
                            'end' => new external_value(PARAM_TEXT, 'nome da postagem', VALUE_DEFAULT, ''),
                            'files' => new external_value(PARAM_TEXT, 'número máximo de arquivos aceitos', VALUE_DEFAULT, '')
                        )
                    )
                    )
                
                )
                
        	);
      
	}
	public static function inserir_returns(){ global $CFG; 
	    return new external_value(PARAM_TEXT, 'The welcome message + user first name');
	                   
	}
   	public static function inserir($course, $section, $name, $intro='', $banca = 0, $stagegrade = 0,$importfrom ='', $importfromassign ='',$availablefrom ='', $availableto ='', $level, $showintro = 0,$showavailability = 0, $formtypes = '', $postagens=''){
         //Parameter validation
        //REQUIRED
        //$params = self::validate_parameters(self::inserir_parameters(),
               // array('course' => $course));
	global $DB;
        $sections = $DB->get_records('course_sections', array('course'=>$course), 'id', '*', 0, 999999);
	$i = 1;	
	
	foreach($sections as $sec){
		if($i === (int)$section){
			$correctSection = $sec;
		}	
		$i++;
	}

	$form=[ 'course'=>$course, 
		'section'=>$correctSection->id, 
		'name'=>$name, 
		'intro'=>$intro, 
		'banca'=>$banca,
		'stagegrade'=>$stagegrade,
		'importfrom'=>$importfrom,
		'importfromassign'=>$importfromassign,
		'availablefrom'=>$availablefrom,
		'availableto'=>$availableto,
		'level'=>$level,
		'showIntro'=>$showintro,
		'showAvailability'=>$showavailability,
		
		];
		if($formT !== ''){
			$formT = explode(',',$formtypes);
			foreach($formT as $indice=>$ft){
				$ind = $indice+1;
				$form['form_'.$ind]=$ft;
			}
		}
		if($postagens !== ''){
			foreach($postagens as $indice=>$post){
				$ind = $indice +1;
				$form['Ipostagem' . $ind] = $post['name'];
				$form['pInicio' . $ind] = $post['begin'];
				$form['pFim' . $ind] = $post['end'];
				$form['pNumeroArquivos' . $ind] = $post['files'];
			}
		}
		$mtcc = new M_TCC();
		
        	return $mtcc->inserir($form);
           
        }
        
        public static function atualizar_parameters(){
	    return new external_function_parameters(
                array(
                'id' => new external_value(PARAM_INT, 'id do módulo tcc', VALUE_DEFAULT, ''),
                'course' => new external_value(PARAM_INT, 'CURSO', VALUE_DEFAULT, ''),
                'section' => new external_value(PARAM_INT, 'section', VALUE_DEFAULT, ''),
                'name' => new external_value(PARAM_TEXT, 'nome', VALUE_DEFAULT, ''),
                'intro' => new external_value(PARAM_TEXT, 'intro', VALUE_DEFAULT, ''),
                'banca' => new external_value(PARAM_INT, 'banca', VALUE_DEFAULT, '0'),
                'stagegrade' => new external_value(PARAM_INT, 'nota por postagem', VALUE_DEFAULT, '0'),
                'importfrom' => new external_value(PARAM_INT, 'importar de outra atividade de tcc', VALUE_DEFAULT, '0'),
                'importfromassign' => new external_value(PARAM_INT, 'importar arquivos de assign', VALUE_DEFAULT, '0'),
                'availablefrom' => new external_value(PARAM_TEXT, 'disponível a partir', VALUE_DEFAULT, ''),
                'availableto' => new external_value(PARAM_TEXT, 'disponível a partir', VALUE_DEFAULT, ''),
                'level' => new external_value(PARAM_INT, 'nível', VALUE_DEFAULT, '1'),
                'showintro' => new external_value(PARAM_INT, 'Mostrar Descrição', VALUE_DEFAULT, '0'),
                'showavailability' => new external_value(PARAM_INT, 'mostrar disponibilidade', VALUE_DEFAULT, '0'),
                'formtypes' => new external_value(PARAM_TEXT, 'formulários obrigatórios', VALUE_DEFAULT, ''),
                'postagens' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_TEXT, 'nome da postagem', VALUE_DEFAULT, ''),
                            'begin' => new external_value(PARAM_TEXT, 'nome da postagem', VALUE_DEFAULT, ''),
                            'end' => new external_value(PARAM_TEXT, 'nome da postagem', VALUE_DEFAULT, ''),
                            'files' => new external_value(PARAM_TEXT, 'número máximo de arquivos aceitos', VALUE_DEFAULT, '')
                        )
                    )
                    )
                
                )
                
        	);
      
	}
	public static function atualizar_returns(){ global $CFG; 
	    return new external_value(PARAM_TEXT, 'The welcome message + user first name');
	                   
	}
   	public static function atualizar($id, $course, $section, $name, $intro='', $banca = 0, $stagegrade = 0,$importfrom ='', $importfromassign ='',$availablefrom ='', $availableto ='', $level, $showintro = 0,$showavailability = 0, $formtypes = '', $postagens=''){
         //Parameter validation
        //REQUIRED
        //$params = self::validate_parameters(self::inserir_parameters(),
               // array('course' => $course));
        global $DB;
        $sections = $DB->get_records('course_sections', array('course'=>$course), 'id', '*', 0, 999999);
	$i = 1;	
	
	foreach($sections as $sec){
		if($i === (int)$section){
			$correctSection = $sec;
		}	
		$i++;
	}
	$module = $DB->get_record('modules', array('name'=>'tcccafeead'));
	$course_module = $DB->get_record('course_modules', array('course'=>$course,'instance'=>$id, 'module'=>$module->id));
	$form=[ 
		'update'=>$course_module->id,
		'course'=>$course, 
		'section'=>$correctSection->id, 
		'name'=>$name, 
		'intro'=>$intro, 
		'banca'=>$banca,
		'stagegrade'=>$stagegrade,
		'importfrom'=>$importfrom,
		'importfromassign'=>$importfromassign,
		'availablefrom'=>$availablefrom,
		'availableto'=>$availableto,
		'level'=>$level,
		'showIntro'=>$showintro,
		'showAvailability'=>$showavailability,
		
		];
		if($formT !== ''){
			$formT = explode(',',$formtypes);
			foreach($formT as $indice=>$ft){
				$ind = $indice+1;
				$form['form_'.$ind]=$ft;
			}
		}
		if($postagens !== ''){
			foreach($postagens as $indice=>$post){
				$ind = $indice +1;
				$form['Ipostagem' . $ind] = $post['name'];
				$form['pInicio' . $ind] = $post['begin'];
				$form['pFim' . $ind] = $post['end'];
				$form['pNumeroArquivos' . $ind] = $post['files'];
			}
		}
		$mtcc = new M_TCC();
		
        	return $mtcc->atualizar($form);
           
        }
        
       public static function excluir_parameters(){
	    return new external_function_parameters(
                array('id' => new external_value(PARAM_INT, 'id do módulo tcc', VALUE_DEFAULT, ''))
            );
      
	}
	public static function excluir_returns(){ global $CFG; 
	    return new external_value(PARAM_TEXT, 'The welcome message + user first name');
	                   
	}
   	public static function excluir($id){
        	return 1234;
        }

    }