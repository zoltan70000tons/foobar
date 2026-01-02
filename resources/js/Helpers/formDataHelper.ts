export const sanitizeFormData = (data) => {
  const sanitized = {};

  Object.entries(data).forEach(([key, value]) => {
    if (key.startsWith("passenger_") && value?.amount !== undefined) {
      sanitized[key] = {
        ...value,
        amount: Number(String(value.amount).replace(/,/g, "")),
        //same with balance and cost when needed
      };
    } else {
      sanitized[key] = value;
    }
  });

  return sanitized;
};
