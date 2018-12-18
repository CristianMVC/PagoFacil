<?php

/* Ejemplo parseo de archivo de transmison (Diseño SEPSA)
*/

class PagoFacil{
    
 private $valores;   
 private $cabecera;
 private $cabecera_lote;
 private $detalle;
 private $cola_lote;
 
 public function __construct($archivo){
    
    try{
    $líneas = file($archivo);
    }catch(Exception $e){
      echo $e->getMessage();  
      exit();          
    }
    
        
    foreach ($líneas as $num_línea => $línea) {
    $this->valores[] = $línea;
    }
    
    
 }
 
   
 /* Obtiene cabecera
    retorna un array
 */   
 public function obtenerCabecera(){
    
   $primer = explode("PAGO FACIL",$this->valores[0]); 
   $this->cabecera['record_code'] = substr($primer[0],0,1 );
   $create_date = new DateTime(substr($primer[0],1,8 ));
   $this->cabecera['create_date'] = $create_date->format('d-m-Y');
   $this->cabecera['origin_name'] = "PAGO FACIL";
   $numerosYletras = $this->separar($primer[1]);
   $this->cabecera['client_number'] =  trim($numerosYletras['numeros']);
   $this->cabecera['client_name'] = trim($numerosYletras['letras']);
    
   return $this->cabecera;
 }   
  
   /* Obtiene cabecera del lote
    retorna un array
 */   
  public function obtenerCabeceraDelLote(){
    
    $numerosYletras = $this->separar($this->valores[1]);
    $this->cabecera_lote['Record_code'] = substr($numerosYletras['numeros'],0,1 );
    $create_date = new DateTime(substr($numerosYletras['numeros'],1,8));
    $this->cabecera_lote['Create_date'] = $create_date->format('d-m-Y');
    $this->cabecera_lote['Batch_number'] = substr($numerosYletras['numeros'],-1 );
    $this->cabecera_lote['Description'] = trim($numerosYletras['letras']);
    
    return $this->cabecera_lote;
  }
    
   /* Obtiene detalle
    retorna un array
 */
  public function obtenerDetalle(){
    
    
    for($i=2; $i < (count($this->valores) - 2); $i++){
               
        if(strpos($this->valores[$i],"PES0")){
          $primer[] = $this->valores[$i];
             
        }
    }
    
     foreach($primer as $p){
        
        $proceso = new DateTime(trim(substr($p,8,8 )));
        $creacion = new DateTime(trim(substr($p,16,8 )));
        $aux = explode("PES",$p);
        $importe = substr_replace(substr($aux[1],0,10),",",-2).substr($aux[1],8,2)."$";
        $num_cliente = trim(substr($aux[0],24 ));
        
        $this->detalle[$num_cliente]['record_code'] = 5;
        $this->detalle[$num_cliente]['work_date'] = $proceso->format('d-m-Y'); 
        $this->detalle[$num_cliente]['transfer_date'] = $creacion->format('d-m-Y');  
        $this->detalle[$num_cliente]['importe'] = $importe;  
        
     }
  
     return $this->detalle;
    
  }
  
   /* Obtiene cola del lote
    retorna un array
 */
  public function obtenerColaDelLote(){
  
      $position = count($this->valores) - 2;    
      
      $this->cola_lote['record_code'] = 8;
      $fecha =  new DateTime(substr(trim($this->valores[$position]),1,8));
      $this->cola_lote['create_date'] = $fecha->format('d-m-Y');
      $this->cola_lote['batch_number'] = substr(trim($this->valores[$position]),9,6);
      $this->cola_lote['batch_payment_count'] = trim(substr($this->valores[$position],15,7));
      $this->cola_lote['batch_payment_amount'] = substr_replace(trim(substr($this->valores[$position],22,12)),",",-2).substr($this->valores[$position],32,2)."$";
      $this->cola_lote['batch_count'] = substr(trim($this->valores[$position]), -5);
      
      return $this->cola_lote;
  }
    
    
  private function separar($string){
    $numANDlet[] = null;
    for( $i = 0; $i < strlen($string); $i++ )
    {
        if( is_numeric($string[$i]) )
        {
            @$numANDlet['numeros'] .= $string[$i];
        }else
        {
            @$numANDlet['letras'] .= $string[$i];
        }
    } 
    
    return $numANDlet;
    
  }  
    
    
    
}


//Ejemplo de uso:

$pago = new PagoFacil('PF310517.406');

echo"<h3>Cabecera archivo</h3>";
echo "<pre>";
print_r($pago->obtenerCabecera());
echo "</pre>";

echo"<h3>Cabecera del lote</h3>";
echo "<pre>";
print_r($pago->obtenerCabeceraDelLote());
echo "</pre>";

echo"<h3>Registro detalle</h3>";
echo "<pre>";
print_r($pago->obtenerDetalle());
echo "</pre>";

echo"<h3>Registro cola del lote</h3>";
echo "<pre>";
print_r($pago->obtenerColaDelLote());
echo "</pre>";

/*
No agregué algunos registros como codigo de barras o registro cola de archivo, ya que me parecio redundante.'
*/



?>