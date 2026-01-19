import * as React from 'react';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import { TeamsList } from './TeamsList';
import { AddTeamDialog } from './AddTeamDialog';

export function TeamsPage() {
  const [isAddDialogOpen, setIsAddDialogOpen] = React.useState(false);
  const [refreshTrigger, setRefreshTrigger] = React.useState(0);

  const handleTeamAdded = () => {
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
          <h2 className="text-3xl font-bold">Teams</h2>
          <p className="text-muted-foreground">
            Manage your sports teams
          </p>
        </div>
        <Button onClick={handleOpenDialog}>
          <Plus className="h-4 w-4 mr-2" />
          Add Team
        </Button>
      </div>

      <TeamsList refreshTrigger={refreshTrigger} />

      <AddTeamDialog
        open={isAddDialogOpen}
        onOpenChange={setIsAddDialogOpen}
        onTeamAdded={handleTeamAdded}
      />
    </div>
  );
}
