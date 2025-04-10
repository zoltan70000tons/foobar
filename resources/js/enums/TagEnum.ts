// enums/TagEnum.ts

export enum TagEnum {
  NEW = "NEW",
  STAFF = "STAFF", 
  ARTIST = "ARTISTS", 
  PRESS = "PRESS", 
  UNUSABLE = "UNUSABLE",
  NON_REV = "NON-REV", 
  POTENTIAL_NOSE = "POTENTIAL NOISE",
  NOT_ASSIGNED = "NOT ASSIGNED",
  ASSIGNED = "ASSIGNED",
  RCCL = "RCCL",
  MISSING_INFO = "MISSING INFO",
  PAID = "PAID",
  IN_MANIFEST = "IN MANIFEST",
  OVERDUE = "OVERDUE",
}

// I dont know if we are using it anohter, place but not in booking detail REMOVE ??
export enum BookingTagEnum { 
  NOT_ASSIGNE = "NOT ASSIGNED", 
  NEW = "NEW", 
  OVERDUE = "OVERDUE", 
  MISSING_INFO = "MISSING INFO",
  PAID = "PAID", 
  IN_MANIFEST = "IN MANIFEST"
}

// Tag styles for TagEnum
export const TagEnumStyles: { [key in TagEnum]: { label: string; color: string } } = {
  [TagEnum.NEW]: { label: "NEW", color: "#66bb6a" },
  [TagEnum.STAFF]: { label: "STAFF", color: "#1976d2" },
  [TagEnum.ARTIST]: { label: "ARTISTS", color: "#9c27b0" },
  [TagEnum.PRESS]: { label: "PRESS", color: "#00bcd4" },
  [TagEnum.UNUSABLE]: { label: "UNUSABLE", color: "#f44336" },
  [TagEnum.NON_REV]: { label: "NON-REV", color: "#607d8b" },
  [TagEnum.POTENTIAL_NOSE]: { label: "POTENTIAL NOISE", color: "#ff9800" },
  [TagEnum.NOT_ASSIGNED]: { label: "NOT ASSIGNED", color: "#9e9e9e" },
  [TagEnum.ASSIGNED]: { label: "ASSIGNED", color: "#4caf50" },
  [TagEnum.RCCL]: { label: "RCCL", color: "#3f51b5" },
  [TagEnum.MISSING_INFO]: { label: "MISSING INFO", color: "#ff9800" },
  [TagEnum.PAID]: { label: "PAID", color: "#2196f3" },
  [TagEnum.IN_MANIFEST]: { label: "IN MANIFEST", color: "#673ab7" },
  [BookingTagEnum.OVERDUE]: { label: "OVERDUE", color: "#f44336" },
};

// Tag styles for BookingTagEnum
export const BookingTagEnumStyles: { [key in BookingTagEnum]: { label: string; color: string } } = {
  [BookingTagEnum.NOT_ASSIGNE]: { label: "NOT ASSIGNED", color: "#757575" },
  [BookingTagEnum.NEW]: { label: "NEW", color: "#4caf50" },
  [BookingTagEnum.OVERDUE]: { label: "OVERDUE", color: "#f44336" },
  [BookingTagEnum.MISSING_INFO]: { label: "MISSING INFO", color: "#ff9800" },
  [BookingTagEnum.PAID]: { label: "PAID", color: "#2196f3" },
  [BookingTagEnum.IN_MANIFEST]: { label: "IN MANIFEST", color: "#673ab7" },
};
