export interface Cabin {
    id: number;
    cabin_type_id: number;
    cabin_category_id: number;
    cabin_number: number;
    cabin_code: string;
    deck:number;
    total_berths:number;
    lower_bed_type_1:string;
    lower_bed_type_2:string;
    upper_berths:string;
    accesible:boolean;
    connect_with:string;
    location:string;
    balcony:boolean;
    obstructed_view:boolean;
    inventory:number;
    notes:string;
    tags:string;
    status:string;
    created_at:string;
    updated_at:string;
    cabin_type:string;
  }