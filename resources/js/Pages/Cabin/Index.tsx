import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";
import { PageProps } from "@/types";
import {
  Tabs,
  Tab,
  Container,
  Grid,
  Toolbar,
  Typography,
  Box,
  AppBar,
} from "@mui/material";
import { usePermissions } from "@/Providers/PermissionContext";
import AllTabContent from './partials/AllTabContent';
import CategoriesTabContent from './partials/CategoriesTabContent';


function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`tabpanel-${index}`}
      aria-labelledby={`tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box p={3}>
          {children}
        </Box>
      )}
    </div>
  );
}

function a11yProps(index: number) {
  return {
    id: `tab-${index}`,
    "aria-controls": `tabpanel-${index}`,
  };
}
const Index = ({ auth, tab, data }: PageProps & { tab: string, data: any }) => {
  const { hasPermission } = usePermissions();
  const [value, setValue] = useState(tab === 'ALL' ? 0 : tab === 'CATEGORIES' ? 1 : tab === 'TAGS' ? 2 : 3);
  const [tabContent, setTabContent] = useState(data);


  const handleChange = (event: React.SyntheticEvent, newValue: number) => {
    setValue(newValue);
  
    let routeName = '';
  
    switch (newValue) {
      case 0:
        routeName = 'cabins.index';
        break;
      case 1:
        routeName = 'cabins.categories';
        break;
      case 2:
        routeName = 'cabins.tags';
        break;
      case 3:
        routeName = 'cabins.deleted';
        break;
      default:
        routeName = 'cabins.index';
        break;
    }
  
    router.get(route(routeName), {}, {
      preserveScroll: true,
      preserveState: true, 
      only: ['data', 'tab'], 
      onSuccess: (page) => {
        setTabContent(page.props.data);
      }
    });
};

  
  

  return (
    <AuthenticatedLayout user={auth.user} header={"Cabins"}>
      <Head title="Cabins" />
      <Toolbar />
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <div>
              <Tabs value={value} onChange={handleChange} aria-label="simple tabs example">
                <Tab label="ALL" {...a11yProps(0)} />
                <Tab label="CATEGORIES" {...a11yProps(1)} />
                <Tab label="TAGS" {...a11yProps(2)} />
                <Tab label="DELETED" {...a11yProps(3)} />
              </Tabs>
              <TabPanel value={value} index={0}>
                <AllTabContent data={tabContent} /> 
              </TabPanel>
              <TabPanel value={value} index={1}>
                <CategoriesTabContent data={tabContent} />
              </TabPanel>
              <TabPanel value={value} index={2}>
                {/* <TagsTabContent data={tabContent} /> */}
              </TabPanel>
              <TabPanel value={value} index={3}>
                {/* <DeletedTabContent data={tabContent} /> */}
              </TabPanel>
            </div>
          </Grid>
        </Grid>
      </Container>
    </AuthenticatedLayout>
  );
};

export default Index;
