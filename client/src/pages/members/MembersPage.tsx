import * as React from 'react';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import { MembersList } from './MembersList';
import { AddMemberDialog } from './AddMemberDialog';

export function MembersPage() {
  const [isAddDialogOpen, setIsAddDialogOpen] = React.useState(false);
  const [refreshTrigger, setRefreshTrigger] = React.useState(0);

  const handleMemberAdded = () => {
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
          <h2 className="text-3xl font-bold">Members</h2>
          <p className="text-muted-foreground">
            Manage your association members
          </p>
        </div>
        <Button onClick={handleOpenDialog}>
          <Plus className="h-4 w-4 mr-2" />
          Add Member
        </Button>
      </div>

      <MembersList refreshTrigger={refreshTrigger} />

      <AddMemberDialog
        open={isAddDialogOpen}
        onOpenChange={setIsAddDialogOpen}
        onMemberAdded={handleMemberAdded}
      />
    </div>
  );
}
