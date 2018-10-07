# Github Bot
A robot for manage github repository via command of issue comment or pull request comment.   
The robot based on [swoft](https://github.com/swoft-cloud/swoft).

## Commands
All commands should place in a new line, and then the Github-Bot will follow the lines to execute the command.

### `/merge`
The bot will merge the Pull Request automatically.

### `/assign [user]`
Assign the issue or pull request to the users.   
Parameters:   
`user`: *[REQUIRED]* the users who you want to assign.    
Example:    
One user: `/assign @huangzhhui`   
Many user: `/assign @huangzhhui @huangzhhui`


### `/remove-assign [user]`
Remove the users from assignees.   
Parameters:   
`user`: *[REQUIRED]* the users who you want to remove.    
Example:    
One user: `/remove-assign @huangzhhui`   
Many user: `/remove-assign @huangzhhui @huangzhhui`

### `/need-review [user]`
Assign the users as reviewers.
Parameters:   
`user`: *[REQUIRED]* the users who you want to assign as reviewers.    
Example:    
One user: `/need-review @huangzhhui`   
Many user: `/need-review @huangzhhui @huangzhhui`

### `/release [repository] [version]`
Parameters:   
`repository`: the repository name that you want to release a new version, default value is `self` (means the current repo that you comment).    
`version`: the version that you want to released, default value is `step` (means the next fixed version, e.g. latest version of the repo is 1.1.9, then the versio will released is 1.1.10)   
Example:    
simple command: `/release`   
specified repository: `/release huangzhhui/github-bot`   
specified repository and version: `/release huangzhhui/github-bot 1.0.1`   

### `/distribute`
Distribute the changes of PR to the components, notice that this command build for [swoft](https://github.com/swoft-cloud), not for everyone. 
This command only works when the owners reply in the repositories configs in  `github.distribute.repositories(./config/properties/app.php)`.    
The distribute rules place in `github.distribute.distribute_mapping(./config/properties/app.php)`, this rules guide the robot how to distribute the components. 