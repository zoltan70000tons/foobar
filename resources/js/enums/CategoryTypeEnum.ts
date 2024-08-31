export enum CategoryTypes {
    BALCONY = 'Balcony',
    INTERIOR = 'Interior',
    OCEAN_VIEW = 'Ocean View',
    SUITE = 'Suite',
}

export const CategoryTypeLabels: Record<CategoryTypes, string> = {
    [CategoryTypes.BALCONY]: 'Balcony',
    [CategoryTypes.INTERIOR]: 'Interior',
    [CategoryTypes.OCEAN_VIEW]: 'Ocean View',
    [CategoryTypes.SUITE]: 'Suite',
};
