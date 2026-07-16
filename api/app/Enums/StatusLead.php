<?php

namespace App\Enums;

enum StatusLead: string
{
    case NOVO = "novo";
    case CONTATO = "contato";
    case QUALIFICADO = "qualificado";
    case NEGOCIAÇÃO = "negociacao";
    case CONVERTIDO = "convertido";
    case PERDIDO = "perdido";
}
