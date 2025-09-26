<?php

namespace App\Enums;

enum StatusCabin: string
{
  case AVAILABLE = 'AVAILABLE'; // PUBLICLY AVAILABLE
  case BOOKED = 'BOOKED';
  case RESERVED = 'RESERVED'; // INTERNALLY AVAILABLE
  case CLOSED = 'CLOSED';
  case PARTIALLY_BOOKED = 'PARTIALLY_BOOKED';
}
