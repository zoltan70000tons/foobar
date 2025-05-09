export type Installment = {
  id: number;
  due_date: string;
  passenger_id: number;
  type: "PAYMENT" | "FEE";
  fee_id?: number;
  created_at: string;
  updated_at: string;
};

export type InstallmentStatus = {
  paid_installments: InstallmentItem[];
  remaining_installments: InstallmentItem[];
  next_installment?: InstallmentItem;
  fully_paid: boolean;
};

export type InstallmentItem = {
  installment_id: number;
  type: "PAYMENT" | "FEE";
  amount?: number;
  amount_due?: number;
  original_amount_due: number; // The original amount before any current payments
  added_fees: number; // The amount of fees added to the original next installment
  due_date: string;
};

export type Fee = {
  id: number;
  passenger_id: number;
  type: string;
  amount: string;
  created_at: string;
  updated_at: string;
};
