<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use App\Models\Comentario;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //Crea el comentario
    public function store(Request $request, $game_id, $user_id)
    {
        $game = game::find($game_id);
        $request->validate([
            "contenidocomentario" => "required|string|max:300",
        ], [
            "contenidocomentario.required" => "El comentario no puede estar vacio",
            "contenidocomentario.max" => "Solo puedes escribir hasta 300 caracteres"
        ]);
        $comentario = new Comentario();
        $comentario->user_id = $user_id;
        $comentario->juego_id = $game_id;
        $comentario->contenido = $request->input("contenidocomentario");
        //Vamos a ver cuantos comentarios ha hecho este usuario en este juego
        $contComentariosEnEsteJuego = 1;
        $allComments = Comentario::all();
        foreach ($allComments as $comment) {
            if ($comment->user_id == $user_id) {
                $contComentariosEnEsteJuego++;
            }
        }
        $comentario->comentario_id = $contComentariosEnEsteJuego;




        $comentario->save();

        $comentarios = Comentario::all();

        $contador = 1;
        foreach ($comentarios as $comentario) {
            if ($comentario->user_id == auth()->user()->id && $comentario->juego_id == $game_id) {
                $contador++;
            }
        }
        //Si ha hecho mas de 6 comentarios en este juego en la siguiente no le dejará comentar más 
        if ($contador >= 6) {
            return redirect()->route('games.show', ['game' => $game])->with('erroralcomentar', 'Para evitar spam, se limitan los comentarios por videojuego a 6');
        } else {


            return redirect()->route('games.show', ['game' => $game])->with('comentariocreado', "Has escrito con exito un comentario");
        }
    }

    //Responder a un comentario (será su hijo)
    public function responder(Request $request, $game_id, $user_id, Comentario $comentario)
    {


        //La valida
        $request->validate([


            "contenidocomentario" => "required|max:300",


        ], [

            "contenidocomentario.required" => "La respuesta no puede ser vacia",
            "contenidocomentario.max" => "Las respuestas no pueden exceder los 300 caracteres"

        ]);
        $respuestacomentario = new Comentario();
        $respuestacomentario->user_id = $user_id;
        $respuestacomentario->juego_id = $game_id;
        $respuestacomentario->contenido = $request->input("contenidocomentario");

        $contComentariosEnEsteJuego = 1;
        $allComments = Comentario::all();
        //Como antes verifica cuantos comentarios en este juego tiene
        foreach ($allComments as $comment) {
            if ($comment->user_id == $user_id) {
                $contComentariosEnEsteJuego++;
            }
        }
        $respuestacomentario->comentario_id = $contComentariosEnEsteJuego;
        $respuestacomentario->padre()->associate($comentario);
        //$comentario->hijos()->associate($respuestacomentario);
        $respuestacomentario->save();

        $game = Game::find($game_id);


        $comentarios = Comentario::all();

        //Cuenta
        $contador = 1;
        foreach ($comentarios as $comentario) {
            if ($comentario->user_id == auth()->user()->id && $comentario->juego_id == $game_id) {
                $contador++;
            }
        }
        //A partir del siguiente comentario si tiene 6 comentarios no le dejara comentar
        if ($contador >= 6) {
            return redirect()->route('games.show', ['game' => $game])->with('erroralcomentar', 'Para evitar spam, se limitan los comentarios por videojuego a 6');
        } else {
            return redirect()->route('games.show', ['game' => $game])->with('respuesta', 'Has respondido a un comentario');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //Elimina los comentarios (si tiene respuestas tambien las elimina)
    public function destroy($id)
    {
        $comentario = Comentario::find($id);
        $allComments = Comentario::all();
        foreach ($allComments as $co) {
            if ($co->padre_id == $id) {
                $co->delete();
            }
        }

        $juego_id = $comentario->juego_id;
        $comentario->delete();
        return redirect()->route('games.show', $juego_id)->with("comentarioeliminado", "Has eliminado un comentario exitosamente");
    }
}
