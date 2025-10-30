export enum LogActorType {
  system = "system",
  agent = "agent",
  customer = "customer",
}

export const LogActorTypeLabel = Object.fromEntries(
  Object.entries(LogActorType).map(([key, value]) => [key, value.charAt(0).toUpperCase() + value.slice(1)])
) as {
  [K in keyof typeof LogActorType]: Capitalize<(typeof LogActorType)[K]>;
};
