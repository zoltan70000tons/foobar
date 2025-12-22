import React, { useEffect, useState, useCallback } from "react";
import {
  alpha,
  Box,
  CircularProgress,
  Typography,
  Tab,
  Tabs,
  Grid
} from "@mui/material";
import { red, blue } from "@mui/material/colors";
import IframeComponent from "./IframeComponent";
import ImageComponent from "./ImageComponent";
import { CabinDetail, PriceAndCapacity } from "@/types/cabin";

type CabinDetailedViewProps = {
  cabinDetail: CabinDetail | null;
};

interface TabPanelProps {
  children?: React.ReactNode;
  dir?: string;
  index: number;
  value: number;
}

function TabPanel({ children, value, index }: TabPanelProps) {

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`full-width-tabpanel-${index}`}
      aria-labelledby={`full-width-tab-${index}`}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          {children}
        </Box>
      )}
    </div>
  );
}

// Main Component
export default function CabinDetailedView({ cabinDetail }: CabinDetailedViewProps) {
  const locale = 'en';

  const [tabValue, setTabValue] = useState<number | null>(null);
  const [configs, setConfigs] = useState<PriceAndCapacity[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  useEffect(() => {
    if (!cabinDetail) {
      setConfigs([]);
      setTabValue(null);
      setIsLoading(false);

      return;
    }

    // Category with price_and_availability
    const entries = Object.values(cabinDetail.price_and_availability || {});
    const arr = entries
      .filter((v: any) => v && v.price !== null && v.price !== "-" && Number(v.price) > 0)
      .map((v: any) => v as PriceAndCapacity);

    setConfigs(arr);
    const firstAvailable = arr.find((c) => c.is_available);
    setTabValue(firstAvailable?.capacity ?? arr[0]?.capacity ?? null);

    setIsLoading(false);
  }, [cabinDetail]);

  // handle tab change
  const handleChange = useCallback((event: React.SyntheticEvent, newValue: number) => {
    setTabValue(newValue);
  }, []);

  // Show loading spinner
  if(isLoading) {
    return (
      <Box
        sx={{
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          minHeight: 200,
        }}
      >
        <CircularProgress />
      </Box>
    );
  }

  if (!configs.length) {
    return null;
  }

  const renderBody = (config: PriceAndCapacity) => (
    <>
      {config?.description && (
        <Box
          sx={{
            maxWidth: "600px",
            margin: "0 auto",
            p: 2,
          }}
        >
          <Typography
            align="center"
            variant="body2"
            dangerouslySetInnerHTML={{
              __html:
                config.description?.[locale as keyof typeof config.description] ||
                "",
            }}
            sx={{ m: "10px auto 20px" }}
          />
        </Box>
      )}

      <Grid
        container
        spacing={2}
        alignItems={"center"}
        justifyContent="center"
        sx={{ minWidth: { sm: "auto", xl: 900 } }}
      >
        {config.iframe &&
          (config.iframe.match(/\.(png|jpg)$/i) ? (
            <Grid size={{ xs: 12, md: config.images ? 6 : 12 }}>
              <ImageComponent images={[config.iframe]} />
            </Grid>
          ) : (
            <Grid size={{ xs: 12, md: config.images ? 6 : 12 }}>
              <IframeComponent iframe={config.iframe} />
            </Grid>
          ))}
        <Grid size={{ xs: 12, md: 6 }}>
          <ImageComponent images={config.images || []} />
        </Grid>
      </Grid>
    </>
  );

  return (
    <Box>
      <Box sx={{ width: '100%', typography: 'body1' }}>
        <Tabs
          value={tabValue}
          onChange={handleChange}
          centered={true}
          sx={{
            minWidth: "300px",
          }}
        >
          {configs.map((config, index) => (
            <Tab
              key={index}
              label={`Occupancy ${config.capacity}`}
              value={config.capacity}
              sx={{
                flex: 1,
                textAlign: "center",
                width: "100%",
                mx: 0.5,
                color: config.is_available ? "inherit" : red[500],
                textDecoration: config.is_available ? "none" : "line-through",
                backgroundColor: config.is_available ? alpha(blue[500], 0.1) : alpha(red[500], 0.1),
                "&.Mui-selected": {
                  color: config.is_available ? alpha(blue[500], 1) : alpha(red[500], 1),
                },
              }}
            />
          ))}
        </Tabs>
        {configs.map((config) => (
          <TabPanel
            key={config.capacity}
            value={tabValue ?? 0}
            index={config.capacity}
          >
            {renderBody(config)}
          </TabPanel>
        ))}
        {/**** IMAGE DISCLAIMER ****/}
        <Typography
          component="small"
          align="center"
          variant="caption"
          sx={{
            display: "block",
            width: "100%",
            maxWidth: "400px",
            margin: "10px auto",
            m: "10px auto",
          }}
        >
          Note: All cabin images are representative samples. Actual cabins may differ in appearance.
        </Typography>
      </Box>
    </Box>
  );
};
