#!/usr/bin/php
<?php
/*
    Licenciamento: Este script pode ser utilizado livremente, sem qualquer ônus ou licença.
    Pode ser copiado, modificado, reproduzido livremente, bastando que seja mantido e citado os créditos ao autor.
*/

/*
    Estrutura de diretórios do projeto
        $dirProjeto           /           $projeto       /  $dirProduto  / $dirEscala
        /dados/cepag/PROJETOS/2019_UFPR_CampusMapBotanico/5_NuvemDePontos/500
        /dados/cepag/PROJETOS/2019_UFPR_CampusMapBotanico/6_ModeloDigitalDeSuperficie/500
        /dados/cepag/PROJETOS/2019_UFPR_CampusMapBotanico/7_Ortoimagens/500

    Resultado deverá ser um arquivo Zip com a seguinte nomenclatura UCM-JB-XX-X

    Falta definir o diretório de destino - sugestão abaixo.
        /dados/cepag/PROJETOS/2019_UFPR_CampusMapBotanico/99_Zips/Escala/arquivo.zip

    Script desenvolvido por: Edson Flavio de Souza (edson.flavio@ufpr.br)
    Versão 1.0 - Atualizado em: 09/12/2019. - Versão inicial
    Versão 1.0 - Atualizado em: 10/12/2019. - Reorganizado a estrutura de diretórios para atender demanda
*/
function removerExtensao ($fName) {
// echo "removendo a extensão de $fName\n";
    $posExtenso = strrpos($fName, ".");
    if  ($posExtenso == false) {
        return $fName;
    }
    return substr ($fName, 0, $posExtenso);
}
function checaParametros($argc, $argv, $dirProjeto) {
    if ($argc < 2 ) {
            exit( "\n Este arquivo deve ser utilizado da seguinte forma: ziparq.php <NomedoProjeto> \n");
    }
    if (!is_dir($dirProjeto . "/" . trim($argv[1])) ){
            exit(
                "\n Verifique se as seguintes condições estão sendo atendidas:" . "\n\n" .
                    "1) O parâmetro passado, obrigatoriamente deve ser um diretório a partir de: " . $dirProjeto . "\n".
                    "2) Seu nome não pode conter espaços ou caracteres especiais; \n".
                    "3) O diretório deve ser criado antes da execução deste script, por segurança. \n");
    }else {
            //O nome do projeto que será compactado é definido pela atribuição do Argumento 1 à variável $projeto
            return $argv[1];
    }
}
function criaDiretorioDestino($dirDestino){
        //Checa se o diretório existe
        if (is_dir($dirDestino)){
           return true;
        }
        //Caso não exista, cria o diretório
        if (!is_dir(!empty(trim($dirDestino))) ){
                if (mkdir($dirDestino, 0770, true)){
                        return true;
                }
        }else {
        exit ("\n Não foi possível criar o diretório" .  $dirDestino . "\n");
        }
}
function validaExtensao($extensao){
    $allowed_types = array('dxf', 'prj', 'tfw', 'tif', 'laz');
    if(in_array(strtolower($extensao), $allowed_types)) {
                return true;
            }else {
                return false;
            }
}
function refazIndiceArray($array_old){
    $array_new= array();
    foreach($array_old as $r){
            $array_new[] = $r;
    }
    return $array_new;
}
function mudarDiretorio($dirProjeto){
        if (is_dir($dirProjeto)){
                chdir($dirProjeto);
        }else{
                exit("\n Não foi possível acessar o diretório de Projetos: " .  $dirProjeto . "\n" . "Por favor verifique!! \n ");
        }
}

function criaDiretorioEscalasDestino($dirProjeto, $projeto, $dirEscala){
        $dirDestino = $dirProjeto . "/" . $projeto . "/" . "99_Zips";
        for ($i=0; $i < count($dirEscala); $i++){
                echo "Criando o diretório " . $dirDestino . "/" . $dirEscala[$i] . "\n";
                $dirZip = $dirDestino . "/" . $dirEscala[$i];
                if (criaDiretorioDestino($dirZip)){
                        echo "Diretório " . $dirDestino . "/" . $dirEscala[$i] . " criado com sucesso! \n";
                };
        }
}
function localizarDirEscalas($dirProjeto, $projeto, $dirProduto, $dirEscala){
        $arrArquivosDir = array();
        for ($i=0; $i < count($dirEscala); $i++){
                for ($j=0; $j<count($dirProduto); $j++){
                        array_push($arrArquivosDir, $dirProduto[$j] . "/" . $dirEscala[$i]);
                }
        }
        return $arrArquivosDir;
}
function identificaArquivosZipar($dirProjeto, $projeto , $arrArquivosDir) {
        $arrArquivosZipar = array();
        $localProjeto = $dirProjeto . "/". $projeto;
        for ($i=0; $i < count($arrArquivosDir); $i++){
                $nomeDir = $arrArquivosDir[$i];
                chdir($localProjeto);
                if (is_dir($nomeDir)){
                        $arrArquivosZipar[$nomeDir] = array_slice(scandir($nomeDir),2);
                }
        }
        return $arrArquivosZipar;
}
function validaArquivosZipar($arrArquivosZipar){
        $arquivosZipar = array();
        foreach ($arrArquivosZipar as $key => $value) {
                foreach ($value as $dir => $nome){
                        $posExtensao = strpos($nome , ".");
                        $extensao = substr($nome , $posExtensao+1 , strlen($nome));
                        if (!empty($nome) and is_file($key . "/" . $nome) and validaExtensao($extensao)){
                                $arquivosZipar[]  = [$key , $nome];
                        }
                }
        }
        sort($arquivosZipar);
        $arquivosZipar =  refazIndiceArray($arquivosZipar);
        return $arquivosZipar;
}
function processaArticulacoes($arrArquivosZipar) {
        $articulacoes = array();
        foreach ($arrArquivosZipar as $key => $value) {
                foreach ($value as $dir => $nome){
                        $posExtensao = strpos($nome , ".");
                        $extensao = substr($nome , $posExtensao+1 , strlen($nome));
                        if (!empty($nome) and is_file($key . "/" . $nome) and validaExtensao($extensao)){
                                $articulacoes[] = substr( substr($nome, 4), 0 , -4);
                        }
                }
        }
        sort($articulacoes);
        $articulacoes = refazIndiceArray(array_unique($articulacoes));
        return $articulacoes;
}

/* Cria e cromprime um arquivo zip
Baseado no código de    https://davidwalsh.name/create-zip-php
Com ajustes efetuados por edson.flavio@ufpr.br
 */
function create_zip($dirProjeto, $files = array(), $destination = '', $overwrite = true ) {
    //if the zip file already exists and overwrite is false, return false
    if(file_exists($destination) && !$overwrite) { return false; }
    //vars
    mudarDiretorio($dirProjeto);
/*
    $valid_files = array();
    //if files were passed in...
    if(is_array($files)) {
            //cycle through each file
            foreach($files as $file) {
                    //make sure the file exists
                    if(file_exists($file) && is_readable($file)) {
                            $valid_files[] = $file;
                    }
            }
    }
*/
    $valid_files = array_filter($files, 'file_exists');
    //if we have good files...
    if(count($valid_files)) {
            //create the archive
            $zip = new ZipArchive();
            $overwrite = file_exists($destination)? $overwrite : false;
            if($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                    print_r("Não consegui abri/criar o arquivo $destination \n");
                    return false;
            }
            //add the files
            foreach($valid_files as $file) {
                    echo "Adicionando o arquivo " . $file . "\n";
                    //$zip->addFile($file, $file);
                    $zip->addFile($file,basename($file));
            }
            //debug
            //echo 'The zip archive contains ', $zip->numFiles , ' files with a status of error = ', $zip->status . "\n";
            //close the zip -- done!
            $zip->close();
            //echo "Closed with: " . ($ret ? "true" : "false") . "\n";
            //check to make sure the file exists
            return file_exists($destination);
    }
    else
    {
            return false;
    }
}
// Configuraçãoes necessárias para execução do Script
// Obtém o diretório atual - Usado para testes
//$dirAtual = getcwd();

set_time_limit(600); //in seconds

//Indica a Localização do Projeto
$dirProjeto = "/dados/cepag/PROJETOS";

//Indica os produtos que terão seus arquivos compactados
$dirProduto = ["5_NuvemDePontos", "6_ModeloDigitalDeSuperficie", "7_Ortoimagens"];

//Indica quais são as escalas dos arquivos
$dirEscala = ["500", "1000"];

//Checa se o número de parâmetros está correto e  atribui o nome do projeto à variável
$projeto = checaParametros($argc, $argv, $dirProjeto);

//Cria o destino padrão baseado no nome do projeto
$dirDestino = $dirProjeto . "/" . $projeto . "/" . "99_Zips";

echo "Acessando o diretório de projetos " . $dirProjeto . "\n";
mudarDiretorio($dirProjeto);

echo "Iniciando o processo de criação do diretório de destino " . $dirDestino . " ...\n";
criaDiretorioEscalasDestino($dirProjeto, $projeto, $dirEscala);

echo "Identificando diretórios com as escalas onde estão os arquivos que deverão ser compactados \n";
$arrArquivosDir = localizarDirEscalas($dirProjeto, $projeto, $dirProduto, $dirEscala);

echo "Estes foram os diretórios encontrados que possuem arquivos para compactar \n";
print_r($arrArquivosDir);

echo "Identificando nos diretórios os arquivos que deverão ser compactados \n";
$arrArquivosZipar = identificaArquivosZipar($dirProjeto, $projeto, $arrArquivosDir);
//print_r($arrArquivosZipar);

echo "Criando o Array com as articulacoes \n";
$articulacoes = processaArticulacoes($arrArquivosZipar);
print_r("Array com as articulações encontradas \n");
print_r($articulacoes);

echo "Criando o Array com os arquivos Validados e que devem ser compactados \n";
$arquivosZiparValidados = validaArquivosZipar($arrArquivosZipar);

//print_r($arquivosZiparValidados);
//Para cada articulação será gerado um arquivo Zip com todos os arquivos pertencentes aos 3 produtos e nas escalas disponiveis
foreach ($dirEscala as $escala){
        foreach ($articulacoes as $articulacao){
        //Inicializa o array que conterá os arquivos que serão compactados
        $arrArquivosArt = array();
        foreach ($arquivosZiparValidados as $key => $value) {
                //$value[0] = 5_NuvemDePontos/500  $value[1]=nuv_UCM-JB-01.laz
                $caminho = explode("/", $value[0]);
                $nome_produto = $caminho[0];
                $escala_arquivo = $caminho[1];
                $nome_arquivo_zip = $articulacao . ".zip";
                //Checando se o arquivo pertence a escala desejada
                if  ( $escala == "1000" && $escala_arquivo == "1000") {
                        //nuv_UCM-JB-01.laz
                        $articulacao_ajustada = $articulacao . '.';
                        //$escala_articulacao = $escala . "/" . $articulacao_ajustada;

                        //Montar o nome do arquivo a ser compactado de acordo com a escala
                        $nome_arquivo = $projeto . "/" . $nome_produto . "/" . $escala . "/" . $value[1];
                        //echo "$articulacao " .  substr($value[1], 4, 11)  ." ". $nome_arquivo . " \n"  ;
                        if((strpos($nome_arquivo, $articulacao_ajustada) !== false ) && (strlen($value[1]) === 17)) {
                        //              print_r("Achei o arquivo $nome_arquivo \n");
                                        array_push($arrArquivosArt, $nome_arquivo );
                        }
                }
                if  ( $escala == "500" && $escala_arquivo == "500") {
                        $articulacao_ajustada = $articulacao;
                        $nome_arquivo = $projeto . "/" . $nome_produto . "/" . $escala . "/" . $value[1];
                        if($articulacao == substr($value[1], 4, 11)) {
                        //              print_r("Achei o arquivo $nome_arquivo \n");
                                        array_push($arrArquivosArt, $nome_arquivo );
                        }
                }
        }
        if (!empty($arrArquivosArt) ){
                $fileDest = $dirDestino . "/" . $escala . "/" . $nome_arquivo_zip;
                //echo "Construindo o arquivo $nome_arquivo_zip, da articulacao $articulacao, escala $escala em $dirDestino  com os seguintes arquivos \n \n";
                //print_r($arrArquivosArt);
                //if true, good; if false, zip creation failed
                $resultado = create_zip($dirProjeto, $arrArquivosArt, $fileDest);
                if ($resultado == true){
                        print_r("Arquivo " . $fileDest . " Criado com sucesso!! \n \n");
                }else {
                        print_r("Falha ao criar o Arquivo " . $fileDest . ", Verifique!! \n \n");
                }
                unset($arrArquivosArt);
        }
  }
}
?>
