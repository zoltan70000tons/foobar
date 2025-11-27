"use server";

import { revalidatePath, revalidateTag } from "next/cache";
import { cookies } from "next/headers";

// revalidate index file
export async function revalidateIndex() {
  revalidatePath("/", "layout");
}

// revalidate singleBooking tag
export async function revalidateBooking() {
  revalidateTag("singleBooking");
}

// revalidate singleInvitation tag
export async function revalidateInvitation() {
  revalidateTag("singleInvitation");
}

// checkBooking tag
export async function revalidateCheckBooking() {
  revalidateTag("checkBooking");
}

// revalidate cart
export async function revalidateCart() {
  revalidateTag("cart");
}
// revalidate customer
export async function revalidateCustomer() {
  revalidateTag("customer");
}

// revalidate cabins
export async function revalidateCabins() {
  revalidateTag("cabins");
}

// revalidate event
export async function revalidateEvent() {
  revalidateTag("event");
}

// get access token from cookies
export async function getAccessToken() {
  const cookieStore = await cookies();
  const accessToken = cookieStore.get("access_token")?.value || "";
  return accessToken;
}
