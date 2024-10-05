import React from 'react';
import MenuItems from '@/Components/MenuItems';

const Events = ({ events }) => {
  const [mainDrawerOpen, setMainDrawerOpen] = React.useState(false);

  return (
    <div>
      <MenuItems mainDrawerToggle={setMainDrawerOpen} events={events} />
    </div>
  );
};

export default Events;