<?php

namespace App\Http\Controllers;

use App\Models\Votacion;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VotacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //Index votaciones admin
    public function index()
    {
        //Devuelve lista de votaciomes
        $votacionesList = Votacion::all();
        return view('votacion.index', ['votacionesList' => $votacionesList]);
    }

    //Index votaciones general
    public function votacionesGeneral()
    {
        //Devuelve lista de votaciones que ven los usuarios normales
        $votacionesList = Votacion::all();
        if(count($votacionesList)==0){
return redirect()->route('proyects.index')->with("nohayvotaciones", "Actualmente no hay ninguna votacion.");
        }
        return view('votacion.indexGeneral', ['votacionesList' => $votacionesList]);
    }

    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    //Dirigir a la vista de creacion de votaciones
    public function create()
    {
        return view("votacion.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //Crear una votacion
    public function store(Request $request)
    {
//Las votaciones tienen nombre, descripcion, valor de la opcion 1,valor de la opcion 2,sus respectivos votos y los participantes
        $request->validate([

            "nombre" => "required|max:30",
            "descripcion" => "required|max:120",
            "valor1" => "required|max:30",
            "valor2" => "required|max:30",


        ], [

            "nombre.required" => "El nombre es obligatorio",
            "nombre.max" =>"El nombre solo puede tener un máximo de 30 caracteres",
            "valor1.required" => "El valor1 es obligatorio",
            "descripcion.required" => "La descripcion es obligatorio",
            "descripcion.max" =>"La descripción solo puede tener un máximo de 120 caracteres",
            "valor1.max" =>"El valor A solo puede tener un máximo de 30 caracteres",
            "valor2.max"=>"El valor B solo puede tener un máximo de 30 caracteres",
            "valor2.required" => "El valor2 es obligatorio",

        ]);
        $votacion = new Votacion();
        $votacion->nombre = $request->input("nombre");
        $votacion->descripcion = $request->input("descripcion");
        $votacion->nombreopcion1 = $request->input("valor1");
        $votacion->nombreopcion2 = $request->input("valor2");
        $votacion->valoropcion1=0;
        $votacion->valoropcion2=0;
        $votacion->activo = true;
        //Crea la votacion
        $votacion->save();
        return redirect()->route('votaciones.index')->with('votacioncreada', 'Votacion creada correctamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //Mostrar votaciones
    public function show($id)
    {
        
$votacion=Votacion::find($id);
//Vamos a ver si hay participantes y devolveremos la cantidad
    $decode = json_decode($votacion->participantes,true);
$numparticipantes=0;

if($decode!=null){
    foreach($decode as $i){
 
        $numparticipantes++;
    }
    }

        return view('votacion.show', ['votacion' => $votacion,'numparticipantes'=>$numparticipantes]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //Dirige a la vista de edicion de votacion
    public function edit($id)
    {

        $votacion = Votacion::find($id);
        return view('votacion.edit', ['votacion' => $votacion]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //Este metodo sirve para votar
    public function update(Request $request, $id)
    {

        $user = Auth::user();
        $user_id = $user->id;
        //dd($user_id);
        $request->validate([], []);

        $votacion = Votacion::find($id);



        $participantes = array();

        //Agrega tu voto a la lista
        if ($votacion->participantes == null) {
            $participantes[] = $user_id;
            if ($request->input("valorvotacion") == 'nombreopcion1') {

                $votacion->valoropcion1 = $votacion->valoropcion1 + 1;
            } else {
                $votacion->valoropcion2 = $votacion->valoropcion2 + 1;
            }
        } else {

            $participantes = json_decode($votacion->participantes);
            if (!in_array($user_id, $participantes)) {
                $participantes[] = $user_id;
                if ($request->input("valorvotacion") == 'nombreopcion1') {

                    $votacion->valoropcion1 = $votacion->valoropcion1 + 1;
                } else {
                    $votacion->valoropcion2 = $votacion->valoropcion2 + 1;
                }
            }
        }

        //encodea la lista
        $votacion->participantes = json_encode($participantes);


//Guarda la votacion
        $votacion->save();
        //Te cierra la pestaña de votacion
        echo "<script>window.close();</script>";
        $user = Auth::user();
        $gameList = Game::all();
        $votacionesList = Votacion::all();
        return view('proyect.index', ['gameList' => $gameList, 'user' => $user, 'votacionesList' => $votacionesList]);
    }





    //No deja acceder a la votacion
    public function cerrarvotacion(Request $request,$id)
    {

        $votacion = Votacion::find($id);
        $votacion->activo = false;
        $votacion->save();
        $nombre = $votacion->nombre;
        return redirect()->route('votaciones.index')->with("votacioneliminada", "Se ha cerrado las votaciones de  " . $nombre . " exitosamente");
    }


//Deja acceder a la votacion
    public function activarvotacion(Request $request,$id)
    {

        $votacion = Votacion::find($id);
        $votacion->activo = true;
        $votacion->save();
        $nombre = $votacion->nombre;
        return redirect()->route('votaciones.index')->with("votacioneliminada", "Se ha activado las votaciones de  " . $nombre . " exitosamente");
    }

    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //Elimina la votacion
    public function destroy($id)
    {
        $votacion = Votacion::find($id);
        $nombre = $votacion->nombre;
        $votacion->delete();
        return redirect()->route('votaciones.index')->with("votacioneliminada", "Votacion '" . $nombre . "' eliminada exitosamente");
    }
}
