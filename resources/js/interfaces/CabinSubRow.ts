export interface CabinSubRow {
    id: number;
    deck:number;
    cabin_number: number;
    balcony: boolean;
    obstructed_view:boolean;
    location:string;
    accesible:boolean;
    cabin_status:string;
    is_reserved: boolean;
    ticket_inventory:number;
    cabin_type:string;
    is_shared_cabin_number: boolean;
    cabin_tags:string;
  }

