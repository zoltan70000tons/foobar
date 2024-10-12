// TO DO: ADD COLOR CODE TO EACH STATUS
// TO DO: ADD ICON TO EACH STATUS
// TO DO: ADD BOOLEAN IF IT CAN BE MANUALLY CHANGED OR NOT

export enum CabinStatus {
    AVAILABLE = 'AVAILABLE',
    RESERVED = 'RESERVED',
    BOOKED = 'BOOKED',
    PARTIALLY_BOOKED = 'PARTIALLY_BOOKED',
    CLOSED = 'CLOSED',
}

export enum CabinStatusReduced {
    AVAILABLE = 'AVAILABLE',
    RESERVED = 'RESERVED',
    CLOSED = 'CLOSED',
}

export const CabinStatusColor: { [key in CabinStatus]: string } = {
    [CabinStatus.AVAILABLE]: 'success',
    [CabinStatus.RESERVED]: 'warning',
    [CabinStatus.BOOKED]: 'primary',
    [CabinStatus.PARTIALLY_BOOKED]: 'secondary',
    [CabinStatus.CLOSED]: 'error',
};
