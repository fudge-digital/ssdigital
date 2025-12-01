<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ParentRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $parent;
    public $password; // password orang tua
    public $students; // array siswa (bisa lebih dari 1)
    public $passwordSiswa; // array password siswa

    /**
     * @param  object  $parent
     * @param  string  $password
     * @param  array   $students  ← setiap item bisa punya email, usia, dll.
     * @param  array|null  $studentPasswords
     */
    public function __construct($parent, $password, $students, $passwordSiswa = [])
    {
        $this->parent = $parent;
        $this->password = $password;
        $this->students = $students;
        $this->passwordSiswa = $passwordSiswa;
    }

    public function build()
    {
        return $this->subject('Registrasi Akun Orang Tua dan Siswa')
            ->view('emails.parent_registration');
    }
}
