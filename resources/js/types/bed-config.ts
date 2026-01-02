export type BedConfigId = "SEPARATED" | "JOINED";

export interface BedConfigOption {
  id: BedConfigId;
  value: BedConfigId;
}

export const bedConfigOptions: BedConfigOption[] = [
  { id: "SEPARATED", value: "SEPARATED" },
  { id: "JOINED", value: "JOINED" },
];
