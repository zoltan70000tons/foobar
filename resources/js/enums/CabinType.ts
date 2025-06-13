export enum CabinType {
    PRIVATE_CABIN = 'PRIVATE CABIN',
    SINGLE_TICKET_MALE = 'SINGLE TICKET MALE',
    SINGLE_TICKET_FEMALE = 'SINGLE TICKET FEMALE',
}

export const CabinTypeIds: Record<CabinType, number> = {
  [CabinType.PRIVATE_CABIN]: 1,
  [CabinType.SINGLE_TICKET_MALE]: 2,
  [CabinType.SINGLE_TICKET_FEMALE]: 3,
};