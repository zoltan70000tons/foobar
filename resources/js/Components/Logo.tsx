export default function Logo({ language }: { language: string }) {
  const frontURL = import.meta.env.VITE_FRONTEND_URL;

  return (
    <a
      href={frontURL + "/" + language}
      style={{
        paddingTop: "5px",
      }}
    >
      <img src="/70k_logo.png" alt="70k Logo" width="200" height="39" />
    </a>
  );
}
