import {
  Grid,
  Stack,
  Typography,
  Box,
  Container,
  Divider,
  Accordion,
  AccordionSummary,
  AccordionDetails,
  Button
} from "@mui/material";
import {
  ExpandMore,
  Facebook,
  Instagram,
  Twitter,
  YouTube,
  Telegram,
} from "@mui/icons-material";
import Copyright from "./Copyright";
import CustomLink from "./CustomLink";

export default function Footer() {


  const locale = "en";


  const sections = [
    {
      title: "Event",
      links: [
        { href: `https://70000tons.com/artists/?lang=${locale}`, text: "Artists" },
        {
          href: `https://70000tons.com/voyage/our-destination/?lang=${locale}`,
          text: "Destination",
        },
        {
          href: `https://70000tons.com/voyage/miami-ft-lauderdale/?lang=${locale}`,
          text: "Miami",
        },
        { href: `https://70000tons.com/ship-facts/?lang=${locale}`, text: "Ship" },
        {
          href: `https://70000tons.com/voyage/arrival-departure/?lang=${locale}`,
          text: "Arrival",
        },
      ],
    },
    {
      title: "Support",
      links: [
        {
          href: `https://70000tons.com/make-a-payment/?lang=${locale}`,
          text: "Make a Payment",
        },
        { href: `https://70000tons.com/faq/?lang=${locale}`, text: "FAQ" },
        {
          href: `https://70000tons.com/payment-schedule/?lang=${locale}`,
          text: "Payment Schedule",
        },
        {
          href: `https://70000tons.com/travel-partners/?lang=${locale}`,
          text: "Travel Partners",
        },
      ],
    },
    {
      title: "Legal",
      links: [
        {
          href: `https://70000tons.com/terms-conditions/?lang=${locale}`,
          text: "Terms and Conditions",
        },
        {
          href: `https://70000tons.com/age-requirements/?lang=${locale}`,
          text: "Age Requirements",
        },
        {
          href: `https://70000tons.com/contact-us/privacy-policy/?lang=${locale}`,
          text: "Privacy Policy",
        },
      ],
    },
    {
      title: "Contact",
      content: [
        { text: "--" },
        { text: "Toll Free" },
        { text: "Other Areas" },
      ],
    },
  ];

  return (
    <Box
      component={"footer"}
      sx={{
        zIndex: 1002,
        backgroundColor: "#000",
        color: "#fff",
        px: 4,
        py: 4,
        position: "relative",
      }}
    >
      <Container>
        <Grid container spacing={2} rowSpacing={2}>
          <Grid
            xs={12}
            md={6}
            display="flex"
            justifyContent={{ xs: "center", md: "flex-start" }}
            alignItems="center"
          >
            <img
              src="/70k_logo.png"
              alt="70000TONS OF METAL"
              width={275}
              height={53}
            />
          </Grid>

          <Grid
            xs={12}
            md={6}
            display="flex"
            justifyContent="flex-end"
            alignItems="center"
          >
            <Stack
              direction="row"
              spacing={1}
              sx={{
                width: {
                  xs: "100%",
                  md: "auto",
                },
                justifyContent: {
                  xs: "center",
                  md: "flex-end",
                },
                flexWrap: "wrap",
              }}
            >
              <CustomLink
                href="https://web.facebook.com/70000tons?_rdc=1&_rdr"
                color="inherit"
                type="iconButton"
              >
                <Facebook />
              </CustomLink>
              <CustomLink
                href="https://www.instagram.com/70000tons/"
                color="inherit"
                type="iconButton"
              >
                <Instagram />
              </CustomLink>
              <CustomLink
                href="https://x.com/70000tons"
                color="inherit"
                type="iconButton"
              >
                <Twitter />
              </CustomLink>
              <CustomLink
                href="http://www.youtube.com/70000tons"
                color="inherit"
                type="iconButton"
              >
                <YouTube />
              </CustomLink>
              <CustomLink
                href="https://www.tiktok.com/@70000tons?lang=en"
                color="inherit"
                type="iconButton"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="#fff"
                  width="24px"
                  height="24px"
                  viewBox="0 0 512 512"
                  id="icons"
                >
                  <path d="M412.19,118.66a109.27,109.27,0,0,1-9.45-5.5,132.87,132.87,0,0,1-24.27-20.62c-18.1-20.71-24.86-41.72-27.35-56.43h.1C349.14,23.9,350,16,350.13,16H267.69V334.78c0,4.28,0,8.51-.18,12.69,0,.52-.05,1-.08,1.56,0,.23,0,.47-.05.71,0,.06,0,.12,0,.18a70,70,0,0,1-35.22,55.56,68.8,68.8,0,0,1-34.11,9c-38.41,0-69.54-31.32-69.54-70s31.13-70,69.54-70a68.9,68.9,0,0,1,21.41,3.39l.1-83.94a153.14,153.14,0,0,0-118,34.52,161.79,161.79,0,0,0-35.3,43.53c-3.48,6-16.61,30.11-18.2,69.24-1,22.21,5.67,45.22,8.85,54.73v.2c2,5.6,9.75,24.71,22.38,40.82A167.53,167.53,0,0,0,115,470.66v-.2l.2.2C155.11,497.78,199.36,496,199.36,496c7.66-.31,33.32,0,62.46-13.81,32.32-15.31,50.72-38.12,50.72-38.12a158.46,158.46,0,0,0,27.64-45.93c7.46-19.61,9.95-43.13,9.95-52.53V176.49c1,.6,14.32,9.41,14.32,9.41s19.19,12.3,49.13,20.31c21.48,5.7,50.42,6.9,50.42,6.9V131.27C453.86,132.37,433.27,129.17,412.19,118.66Z" />
                </svg>
              </CustomLink>
              <CustomLink
                href="https://bsky.app/profile/70000tons.bsky.social"
                color="inherit"
                type="iconButton"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 -3.268 64 68.414"
                  style={{ marginTop: "3px" }}
                  width="24"
                  height="24"
                >
                  <path
                    fill="#fff"
                    d="M13.873 3.805C21.21 9.332 29.103 20.537 32 26.55v15.882c0-.338-.13.044-.41.867-1.512 4.456-7.418 21.847-20.923 7.944-7.111-7.32-3.819-14.64 9.125-16.85-7.405 1.264-15.73-.825-18.014-9.015C1.12 23.022 0 8.51 0 6.55 0-3.268 8.579-.182 13.873 3.805zm36.254 0C42.79 9.332 34.897 20.537 32 26.55v15.882c0-.338.13.044.41.867 1.512 4.456 7.418 21.847 20.923 7.944 7.111-7.32 3.819-14.64-9.125-16.85 7.405 1.264 15.73-.825 18.014-9.015C62.88 23.022 64 8.51 64 6.55c0-9.818-8.578-6.732-13.873-2.745z"
                  />
                </svg>
              </CustomLink>
              <CustomLink
                href="https://www.threads.net/@70000tons"
                color="inherit"
                type="iconButton"
              >
                <svg
                  width={22}
                  height={22}
                  xmlns="http://www.w3.org/2000/svg"
                  aria-label="Threads"
                  viewBox="0 0 192 192"
                  fill="#fff"
                >
                  <path d="M141.537 88.9883C140.71 88.5919 139.87 88.2104 139.019 87.8451C137.537 60.5382 122.616 44.905 97.5619 44.745C97.4484 44.7443 97.3355 44.7443 97.222 44.7443C82.2364 44.7443 69.7731 51.1409 62.102 62.7807L75.881 72.2328C81.6116 63.5383 90.6052 61.6848 97.2286 61.6848C97.3051 61.6848 97.3819 61.6848 97.4576 61.6855C105.707 61.7381 111.932 64.1366 115.961 68.814C118.893 72.2193 120.854 76.925 121.825 82.8638C114.511 81.6207 106.601 81.2385 98.145 81.7233C74.3247 83.0954 59.0111 96.9879 60.0396 116.292C60.5615 126.084 65.4397 134.508 73.775 140.011C80.8224 144.663 89.899 146.938 99.3323 146.423C111.79 145.74 121.563 140.987 128.381 132.296C133.559 125.696 136.834 117.143 138.28 106.366C144.217 109.949 148.617 114.664 151.047 120.332C155.179 129.967 155.42 145.8 142.501 158.708C131.182 170.016 117.576 174.908 97.0135 175.059C74.2042 174.89 56.9538 167.575 45.7381 153.317C35.2355 139.966 29.8077 120.682 29.6052 96C29.8077 71.3178 35.2355 52.0336 45.7381 38.6827C56.9538 24.4249 74.2039 17.11 97.0132 16.9405C119.988 17.1113 137.539 24.4614 149.184 38.788C154.894 45.8136 159.199 54.6488 162.037 64.9503L178.184 60.6422C174.744 47.9622 169.331 37.0357 161.965 27.974C147.036 9.60668 125.202 0.195148 97.0695 0H96.9569C68.8816 0.19447 47.2921 9.6418 32.7883 28.0793C19.8819 44.4864 13.2244 67.3157 13.0007 95.9325L13 96L13.0007 96.0675C13.2244 124.684 19.8819 147.514 32.7883 163.921C47.2921 182.358 68.8816 191.806 96.9569 192H97.0695C122.03 191.827 139.624 185.292 154.118 170.811C173.081 151.866 172.51 128.119 166.26 113.541C161.776 103.087 153.227 94.5962 141.537 88.9883ZM98.4405 129.507C88.0005 130.095 77.1544 125.409 76.6196 115.372C76.2232 107.93 81.9158 99.626 99.0812 98.6368C101.047 98.5234 102.976 98.468 104.871 98.468C111.106 98.468 116.939 99.0737 122.242 100.233C120.264 124.935 108.662 128.946 98.4405 129.507Z" />
                </svg>
              </CustomLink>
              <CustomLink
                href="https://t.me/original70000tons"
                color="inherit"
                type="iconButton"
              >
                <Telegram />
              </CustomLink>
              <CustomLink
                href="https://open.spotify.com/user/31wphhaqhspcwfbk7kn2vcicr3wy?si=7b2cd2cac632470d"
                color="inherit"
                type="iconButton"
              >
                <svg
                  width={24}
                  height={24}
                  xmlns="http://www.w3.org/2000/svg"
                  fill="#fff"
                >
                  <path d="M19.098 10.638c-3.868-2.297-10.248-2.508-13.941-1.387-.593.18-1.22-.155-1.399-.748-.18-.593.154-1.22.748-1.4 4.239-1.287 11.285-1.038 15.738 1.605.533.317.708 1.005.392 1.538-.316.533-1.005.709-1.538.392zm-.126 3.403c-.272.44-.847.578-1.287.308-3.225-1.982-8.142-2.557-11.958-1.399-.494.15-1.017-.129-1.167-.623-.149-.495.13-1.016.624-1.167 4.358-1.322 9.776-.682 13.48 1.595.44.27.578.847.308 1.286zm-1.469 3.267c-.215.354-.676.465-1.028.249-2.818-1.722-6.365-2.111-10.542-1.157-.402.092-.803-.16-.895-.562-.092-.403.159-.804.562-.896 4.571-1.045 8.492-.595 11.655 1.338.353.215.464.676.248 1.028zm-5.503-17.308c-6.627 0-12 5.373-12 12 0 6.628 5.373 12 12 12 6.628 0 12-5.372 12-12 0-6.627-5.372-12-12-12z" />
                </svg>
              </CustomLink>
            </Stack>
          </Grid>
        </Grid>
        <Box
          sx={{
            maxWidth: "1536px",
            mx: "auto",
          }}
        >
          <Typography
            variant="h5"
            align="center"
            sx={{
              fontWeight: "bold",
              pt: {
                xs: 3,
                md: 6,
              },
            }}
          >
            BECOME PART OF OUR INTERNATIONAL HEAVY METAL FAMILY!
          </Typography>
          <Stack
            direction="row"
            spacing={2}
            sx={{
              mt: 2,
              width: "100%",
              justifyContent: "center",
            }}
          >
            {/* <NewsletterContainer /> */}
            <Button
              href="https://70000tons.com/forum/"
              type="button"
              variant="modern"
              color="secondary"
              sx={{ fontSize: {xs: "0.75em", sm: "1em", textAlign: "center"} }}
            >
              Forum
            </Button>
          </Stack>
        </Box>
        <Divider
          sx={{
            backgroundColor: "#333",
            opacity: 0.5,
            my: 4,
          }}
        />
        <Box
          sx={{
            maxWidth: "1536px",
            mx: "auto",
          }}
        >
          <Grid container justifyContent="space-between" spacing={2}>
            {sections.map((section, index) => (
              <Grid item xs={12} sm={6} md={3} key={index}>
                <Accordion sx={{ display: { xs: "block", md: "none" } }}>
                  <AccordionSummary
                    expandIcon={<ExpandMore />}
                    aria-controls={`panel${index}-content`}
                    id={`panel${index}-header`}
                  >
                    <Typography
                      variant="subtitle1"
                      fontWeight="bold"
                      sx={{ fontSize: "1rem" }}
                    >
                      {section.title}
                    </Typography>
                  </AccordionSummary>
                  <AccordionDetails>
                    <Stack spacing={1.5}>
                      {section.links
                        ? section.links.map((link, linkIndex) => (
                            <CustomLink href={link.href} key={linkIndex}>
                              {link.text}
                            </CustomLink>
                          ))
                        : section.content.map((item, contentIndex) => (
                            <Typography
                              key={contentIndex}
                              sx={{ fontSize: "0.875rem" }}
                            >
                              {item.text}
                            </Typography>
                          ))}
                    </Stack>
                  </AccordionDetails>
                </Accordion>

                <Stack
                  spacing={1.5}
                  sx={{ display: { xs: "none", md: "flex" } }}
                >
                  <Typography variant="subtitle1" fontWeight="bold">
                    {section.title}
                  </Typography>
                  {section.links
                    ? section.links.map((link, linkIndex) => (
                        <CustomLink href={link.href} key={linkIndex}>
                          {link.text}
                        </CustomLink>
                      ))
                    : section.content.map((item, contentIndex) => (
                        <Typography key={contentIndex}>{item.text}</Typography>
                      ))}
                </Stack>
              </Grid>
            ))}
          </Grid>
        </Box>
        <Divider
          sx={{
            backgroundColor: "#333",
            opacity: 0.5,
            my: 4,
          }}
        />
        <Copyright />
      </Container>
    </Box>
  );
}
