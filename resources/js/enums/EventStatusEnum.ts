export enum EventStatus {
    PRE_SALE = 'pre-sale',
    PUBLIC = 'public',
    CLOSED = 'closed',
    DRAFT = 'draft',
}

export const EventStatusLabels: Record<EventStatus, string> = {
    [EventStatus.PRE_SALE]: 'Pre-sale',
    [EventStatus.PUBLIC]: 'Public',
    [EventStatus.CLOSED]: 'Closed',
    [EventStatus.DRAFT]: 'Draft',
};
