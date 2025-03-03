export enum EventStatus {
    PRE_SALE = 'PRE-SALE',
    PUBLIC = 'PUBLIC',
    CLOSED = 'CLOSED',
    DRAFT = 'DRAFT',
}

export const EventStatusLabels: Record<EventStatus, string> = {
    [EventStatus.PRE_SALE]: 'PRE_SALE',
    [EventStatus.PUBLIC]: 'PUBLIC',
    [EventStatus.CLOSED]: 'CLOSE',
    [EventStatus.DRAFT]: 'DRAFT',
};
