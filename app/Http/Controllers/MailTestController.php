<?php

namespace App\Http\Controllers;

use App\Interfaces\TeamRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteUserMail;
use App\Repositories\TeamRepository;

class MailTestController extends Controller
{
    protected TeamRepositoryInterface $teamRepositoryInterface;
    public function __construct(TeamRepository $teamRepositoryInterface){
        $this->teamRepositoryInterface =$teamRepositoryInterface;
    }

    public function sendMail()
    {
        $data = ['email' => 'leonardo@70000tons.com', 'name' => 'Leonardo Bonilla'];
        $this->teamRepositoryInterface->inviteMember($data);
       // Mail::to('leonardo@70000tons.com')->send(new InviteUserMail($link));
        return 'Correo enviado correctamente!';
    }
}