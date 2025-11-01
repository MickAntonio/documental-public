<?php
use App\Models\Config\ModelSequence;

/**
 * This function provide next sequence by sequence code
 * @param string $code
 * @return string $sequence
 */
function sequenceNextByCode(string $code, string $separator='/'){
    if ($code && isNullOrEmpty($code))
        throw new Exception('Código da sequência é inválido');
    $model_sequence = ModelSequence::where("code", $code)->first();
    if (!$model_sequence)
        throw new Exception('Nenhum registo foi encontrado');
    
    if(!$model_sequence->active)
        throw new Exception('Está sequência encontra-se inválidada');
    
    if($model_sequence->year != date("Y")){
        $model_sequence->year = date("Y");
        $model_sequence->number_next = $model_sequence->number_increment;
        $model_sequence->save();
    }

    $padding = null;
    for($a=0;$a<$model_sequence->padding;$a++)
        $padding = !isset($padding)? '0': $padding . "0";    
    $sequence = $model_sequence->prefix;
    
    if($model_sequence->number_next <= (int) ('1' . $padding)){
        if($model_sequence->number_next < 10)
            $sequence = $sequence . $model_sequence->year .$separator. substr($padding,0,-1);
        elseif($model_sequence->number_next < 100)
            $sequence = $sequence . $model_sequence->year .$separator. substr($padding,0,-2);
        elseif($model_sequence->number_next < 1000)
            $sequence = $sequence . $model_sequence->year .$separator. substr($padding,0,-3);
        elseif($model_sequence->number_next < 10000)
            $sequence = $sequence . $model_sequence->year .$separator. substr($padding,0,-4);
        elseif($model_sequence->number_next < 100000)
            $sequence = $sequence . $model_sequence->year .$separator. substr($padding,0,-5);
        elseif($model_sequence->number_next <= 1000000)
            $sequence = $sequence . $model_sequence->year .$separator. substr($padding,0,-6);
    }else
        throw new Exception('Atingiu o número maximo da sequência');
        
    if($sequence != $model_sequence->prefix){
        $sequence = $sequence . $model_sequence->number_next;
        $sequence_update = ModelSequence::find($model_sequence->id);
        $sequence_update->number_next = $model_sequence->number_next + $model_sequence->number_increment;
        $sequence_update->save();
    }
    return $sequence;    
}

/**
 * This function provide next sequence by sequence code
 * @param string $code
 * @return string $sequence
 */
function sequenceNextByCodeSepator(string $code, string $separator='/'){
    if ($code && isNullOrEmpty($code))
        throw new Exception('Código da sequência é inválido');
    $model_sequence = ModelSequence::where("code", $code)->first();
    if (!$model_sequence)
        throw new Exception('Nenhum registo foi encontrado');
    
    if(!$model_sequence->active)
        throw new Exception('Está sequência encontra-se inválidada');
    
    if($model_sequence->year != date("Y")){
        $model_sequence->year = date("Y");
        $model_sequence->number_next = $model_sequence->number_increment;
        $model_sequence->save();
    }

    $padding = null;
    for($a=0;$a<$model_sequence->padding;$a++)
        $padding = !isset($padding)? '0': $padding . "0";    
    $sequence = $model_sequence->prefix;
    
    if($model_sequence->number_next <= (int) ('1' . $padding)){
        if($model_sequence->number_next < 10)
            $sequence = $sequence . substr($model_sequence->year,2) .$separator. substr($padding,0,-1);
        elseif($model_sequence->number_next < 100)
            $sequence = $sequence . substr($model_sequence->year,2) .$separator. substr($padding,0,-2);
        elseif($model_sequence->number_next < 1000)
            $sequence = $sequence . substr($model_sequence->year,2) .$separator. substr($padding,0,-3);
        elseif($model_sequence->number_next < 10000)
            $sequence = $sequence . substr($model_sequence->year,2) .$separator. substr($padding,0,-4);
        elseif($model_sequence->number_next < 100000)
            $sequence = $sequence . substr($model_sequence->year,2) .$separator. substr($padding,0,-5);
        elseif($model_sequence->number_next <= 1000000)
            $sequence = $sequence . substr($model_sequence->year,2) .$separator. substr($padding,0,-6);
    }else
        throw new Exception('Atingiu o número maximo da sequência');
        
    if($sequence != $model_sequence->prefix){
        $sequence = $sequence . $model_sequence->number_next;
        $sequence_update = ModelSequence::find($model_sequence->id);
        $sequence_update->number_next = $model_sequence->number_next + $model_sequence->number_increment;
        $sequence_update->save();
    }
    return $sequence;    
}


/**
 * This method revert a sequence if needed by sequence code
 * @param string $code
 * @return bool True/False 
 */
function revertSequence(string $code){
    if ($code && isNullOrEmpty($code))
        throw new Exception('Código da sequência é inválido');
    $model_sequence = ModelSequence::where("code", $code)->first();
    if (!$model_sequence)
        throw new Exception('Nenhum registo foi encontrado');    
    if(!$model_sequence->active)
        throw new Exception('Está sequência encontra-se inválidada');
    
    $model_sequence->number_next = $model_sequence->number_next - $model_sequence->number_increment;
    return $model_sequence->save();
}
