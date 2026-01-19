import * as React from 'react';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import { EventsList } from './EventsList';
import { AddEventDialog } from './AddEventDialog';

export function EventsPage() {
  const [isAddDialogOpen, setIsAddDialogOpen] = React.useState(false);
  const [refreshTrigger, setRefreshTrigger] = React.useState(0);

  const handleEventAdded = () => {
    setRefreshTrigger(prev => prev + 1);
    setIsAddDialogOpen(false);
  };

  const handleOpenDialog = () => {
    setIsAddDialogOpen(true);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold">Events</h2>
          <p className="text-muted-foreground">
            Manage training sessions, matches, and other events
          </p>
        </div>
        <Button onClick={handleOpenDialog}>
          <Plus className="h-4 w-4 mr-2" />
          Add Event
        </Button>
      </div>

      <EventsList refreshTrigger={refreshTrigger} />

      <AddEventDialog
        open={isAddDialogOpen}
        onOpenChange={setIsAddDialogOpen}
        onEventAdded={handleEventAdded}
      />
    </div>
  );
}
