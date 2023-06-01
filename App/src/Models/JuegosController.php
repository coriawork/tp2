<?php
namespace App\src\Models;
use App\src\Models\DB;
use Exception;

class JuegosController{
    public function juegosAll($request, $response, $args){
        /*
        Esta funcion recibe un get request y devuelve todos los juegos
        */
        $db = new DB();
        $respuesta = $db->makeQuery('SELECT * FROM juegos')->fetchAll();
        $response->getBody()->write(json_encode($respuesta));
        return $response->withStatus(200);
    }  
    public function juegos (Request $request, Response $response, $args){
        /*
        Esta función recibe un GET request con parámetros para buscar juego.
        Es obligatorio que exista al menos uno de estos tres parámetros:
            -genero(str).
            -plataforma(str).
            -nombre(str).
        Es opcional el siguiente parámetro:
            -orden(boolean): orden predefinido = ASC.
        */

        $db = new DB();
        try {
            $genero = $request->getQueryParams()['genero'] ?? null;
            $nombre = $request->getQueryParams()['nombre'] ?? null;
            $plataforma = $request->getQueryParams()['plataforma'] ?? null;
            if ($genero === null && $plataforma === null && $nombre === null) throw new Exception("se debe dar un parametro (genero o plataforma o nombre)", 400);
            $asc = $request->getQueryParams()['asc']?? false;
            $datos = [];
            $query = "SELECT * FROM juegos WHERE 1=1 ";
            if($genero != null){
                $query.="AND id_genero = ?";
                array_push($datos,$genero);
            }
            if($nombre != null){
                $query .= "AND nombre like ?";
                $nombre = "%".$nombre."%";
                array_push($datos, $nombre);
            }
            if($plataforma!= null){
                $query.="AND plataforma =?";
                array_push($datos, $plataforma);
            }
            if($asc)$query.=" ORDER BY nombre ASC ";
            $respuesta = $db->makeQuery($query, $datos)->fetchAll();
            if(count($respuesta) === 0)throw new Exception("No se encontro el juego", 404);
            $response->getBody()->write(json_encode($respuesta));
            $db->close();
            return $response->withStatus(200);
        }
        catch (Exception $e) {
            $response->getBody()->write($e->getMessage());
            $db->close();
            return $response->withStatus(404);
        }
    }
    public function createJuego ($request, $response, $args) {
        /*
        Esta función recibe un POST request para agregar un juego nuevo.
        Respeta y valida las condiciones previamente establecidas en la Entrega nº1 en cuanto
        a la creación de un nuevo juego vía formulario de html.
        Por tanto, es obligatorio que la request contenga los siguientes campos:
            nombre(str),
            imagen(blob en base64),
            tipo_imagen(str): debe ingresarse un tipo válido,
            id_plataforma(int): debe ser un id válido de una plataforma existente
        Los siguientes campos son opcionales:
            descripcion(str): no más de 255 caracteres
            url(str): no más de 80 caracteres
            id_genero(): debe ser un id válido de un genero existente
        Si la creación del juego es exitosa se imprime un mensaje junto con un Status200 en la response.
        */
        $db = new DB();
        $body = json_decode($request->getBody(), true);
        try {
            if (!isset($body['nombre'], $body['url'], $body['imagen'], $body['tipo_imagen'], $body['descripcion'], $body['id_genero'], $body['id_plataforma'])) throw new Exception("no se recibieron todos los parametros", 400);
            $params = array(
                ':v1' => $body['nombre'],
                ':v2' => $body['imagen'],
                ':v3' => $body['tipo_imagen'],
                ':v4' => $body['descripcion'],
                ':v5' => $body['url'],
                ':v6' => $body['id_genero'],
                ':v7' => $body['id_plataforma']
            );
            
            $query = 'INSERT INTO juegos (nombre, imagen, tipo_imagen, descripcion, url, id_genero, id_plataforma) VALUES (:v1,:v2,:v3,:v4,:v5,:v6,:v7)';
            $db->makeQuery($query,$params);
            return $response->withStatus(200);
        }
        catch (Exception $e) {
            $response->getBody()->write($e->getMessage());
            return $response->withStatus($e->getCode());
        }
    }

    //* actualizar juego con id 
    public function updateJuegos($request, $response, $args)
    {
        $db = new DB();
        try {
            if (!is_numeric($args['id'])) throw new Exception("El id debe ser numerico", 400);
            if (!isset($args['id'])) throw new Exception("No se recibio el id para hacer el update", 400);
            if (!$db->existsIn('juegos', $args['id'])) throw new Exception("No se encontro el id: '" . $args['id'] . "'", 404);
            $body = json_decode($request->getBody(), true);
            $query = "UPDATE juegos SET "; // vamos a ir generando la query de a partes
            $bindings = [];
            foreach ($body as $field => $value) { //este for each mapea bindings con campos ingresados 
                $query .= "$field = :$field, ";
                $bindings[":$field"] = $value;
            }
            $query = rtrim($query, ', '); // elimina la última coma de la query agregada en la última iteración del foreach
            $query .= " WHERE id = :id";
            $bindings[':id'] = $args['id'];
            $db->makeQuery($query, $bindings)->fetchAll(); //makeQuery prepara la $query y luego la ejecuta con los $bindings
            $response->getBody()->write("Se actualizo bien");
            return $response->withStatus(200);
        } 
        catch (Exception $e) {
            $response->getBody()->write($e->getMessage());
            return $response->withStatus(404);
        }
    }
    public function delete($request, $response, $args){
        $db = new DB();
        try {
            $body = json_decode($request->getBody(), true);
            $result = $db->makeQuery("SELECT * from juegos where id = '" . $body['id'] . "'");
            if($result->rowCount() === 0) throw new Exception("No existe el id", 400);
            if (!isset($body['id'])) throw new Exception("No se recibio el id", 400);
            $result = $db->makeQuery("DELETE FROM juegos where id = '".$body['id']."'");
            return $response;
        }
        catch (Exception $e) {
            $response->getBody()->write($e->getMessage());
            return $response->withStatus(400);
        }
    }
}
?>    