export enum StatusEnum {
  NEW = "NEW",
  ON_HOLD = "ON HOLD",
  UPLOADED = "UPLOADED",
}

export enum BookingStatusEnum {
  NEW = "NEW",
  ON_HOLD = "ON HOLD",
  UPLOADED = "UPLOADED",
  CANCELLED = "CANCELLED",
}

export const BookingStatusColor: { [key in BookingStatusEnum]: string } = {
  [BookingStatusEnum.NEW]: "#66bb6a",       
  [BookingStatusEnum.ON_HOLD]: "#ffa726",   
  [BookingStatusEnum.UPLOADED]: "#42a5f5",  
  [BookingStatusEnum.CANCELLED]: "#ef5350",
};