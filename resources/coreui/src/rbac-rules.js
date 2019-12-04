const rules = {
  guest: {
    static: [],
    dynamic: {}
  },
  student: {
      static: [
        "dashboard-page:visit"
      ],
      dynamic: {
        "posts:edit": ({userId, postOwnerId}) => {
          if (!userId || !postOwnerId) return false;
          return userId === postOwnerId;
        }
      }
    },
    coordinator: {
      static: [
        "users:list",
        "users:get",
        "dashboard-page:visit"
      ]
    },
    superuser: {
      static: [
        "users:list",
        "users:create",
        "users:edit",
        "users:delete",
        "users:get",
        "dashboard-page:visit"
      ]
    }
  };
  
  export default rules;