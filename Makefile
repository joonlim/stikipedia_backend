CC=gcc
CFLAGS=-Werror -Wall
BIN=launcher_php
MODULES=utils.c

all: $(BIN)

clean:
	rm -f *.o *.out $(BIN)

$(BIN): clean
	$(CC) $(CFLAGS) -o $(BIN) $(BIN).c $(MODULES)

