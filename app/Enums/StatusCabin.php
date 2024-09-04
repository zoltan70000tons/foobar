<?

namespace App\Enums;

enum StatusCabin: string
{
  case AVAILABLE = 'AVAILABLE';
  case BOOKED = 'BOOKED';
  case RESERVED = 'RESERVED';
  case CLOSED = 'CLOSED';
}
